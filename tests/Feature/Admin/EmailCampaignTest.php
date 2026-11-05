<?php

namespace Tests\Feature\Admin;

use App\Filament\Pages\EmailSender;
use App\Models\User;
use App\Notifications\AdminMailNotification;
use App\Services\Admin\EmailCampaignService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class EmailCampaignTest extends TestCase
{
    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Http::preventStrayRequests(); Mail::fake(); Notification::fake();
        config(['admin_migration.bulk_email_enabled' => false]);
        Schema::create('users', function (Blueprint $table): void {
            $table->id(); $table->string('email'); $table->string('news');
            $table->string('first_name')->nullable(); $table->string('last_name')->nullable();
            $table->integer('type_id'); $table->text('settings'); $table->timestamps();
        });
        Schema::create('user_types', function (Blueprint $table): void { $table->id(); $table->string('name'); });
        Schema::create('locales', function (Blueprint $table): void { $table->id(); $table->string('prefix'); });
        Schema::create('user_messages', function (Blueprint $table): void { $table->id(); $table->text('uns_text'); });
        Schema::create('translations', function (Blueprint $table): void {
            $table->id(); $table->string('table_name'); $table->string('column_name');
            $table->integer('foreign_key'); $table->string('locale'); $table->text('value');
        });
        (require database_path('migrations/2026_10_08_230000_create_admin_email_campaigns_table.php'))->up();
        DB::table('user_types')->insert([['id' => 1, 'name' => 'Client'], ['id' => 2, 'name' => 'Company']]);
        DB::table('locales')->insert([['prefix' => 'ru'], ['prefix' => 'en']]);
        DB::table('user_messages')->insert(['id' => 1, 'uns_text' => 'Unsubscribe']);
        DB::table('translations')->insert(['table_name' => 'user_messages', 'column_name' => 'uns_text',
            'foreign_key' => 1, 'locale' => 'ru', 'value' => 'Отписаться']);
        foreach ([[1,'a@example.invalid','YES',1,'ru'],[2,'b@example.invalid','YES',2,'en'],
            [3,'c@example.invalid','NO',1,'ru'],[4,'invalid','YES',1,'ru'],[5,'e@example.invalid','YES',1,'en']] as [$id,$email,$news,$type,$locale]) {
            DB::table('users')->insert(['id' => $id, 'email' => $email, 'news' => $news, 'type_id' => $type,
                'first_name' => 'Fixture', 'settings' => json_encode(['locale' => $locale])]);
        }
        $this->actor = \Mockery::mock(User::class)->makePartial();
        $this->actor->id = 101; $this->actor->shouldReceive('hasPermission')->andReturn(true);
    }

    private function data(array $overrides = []): array
    {
        return array_merge(['users' => [], 'user_types' => [], 'locales' => [], 'subject' => 'Offer',
            'greetings' => 'Hello', 'line' => '<p>Test body</p>', 'salutation' => 'Goodbye'], $overrides);
    }

    public function test_filters_intersect_and_preview_is_actual_message_without_sending(): void
    {
        $prepared = app(EmailCampaignService::class)->prepare($this->actor, $this->data(['users' => [1,2,5], 'user_types' => [1], 'locales' => ['en']]));
        $this->assertSame(1, $prepared['count']);
        $this->assertSame('e@example.invalid', $prepared['sample']);
        foreach (['Test body', 'Goodbye', 'Unsubscribe', '/user/5/unsubscribe'] as $text) { $this->assertStringContainsString($text, $prepared['html']); }
        Notification::assertNothingSent(); Mail::assertNothingSent(); Http::assertNothingSent();
    }

    public function test_disabled_campaign_and_duplicate_never_send(): void
    {
        $service = app(EmailCampaignService::class);
        $prepared = $service->prepare($this->actor, $this->data());
        $this->assertSame(3, $prepared['count']);
        $result = $service->send($this->actor, $prepared['token']);
        $this->assertSame('suppressed', $result['status']);
        $this->assertSame(3, $result['suppressed']);
        config(['admin_migration.bulk_email_enabled' => true]);
        $this->assertTrue($service->send($this->actor, $prepared['token'])['duplicate']);
        Notification::assertNothingSent();
    }

    public function test_enabled_fake_queue_rechecks_subscription_and_email_and_replay(): void
    {
        config(['admin_migration.bulk_email_enabled' => true, 'queue.default' => 'database']);
        $service = app(EmailCampaignService::class);
        $prepared = $service->prepare($this->actor, $this->data());
        DB::table('users')->where('id', 2)->update(['news' => 'NO']);
        DB::table('users')->where('id', 5)->update(['email' => 'changed@example.invalid']);
        $result = $service->send($this->actor, $prepared['token']);
        $this->assertSame(1, $result['queued']); $this->assertSame(2, $result['skipped']);
        Notification::assertSentTo(User::find(1), AdminMailNotification::class);
        $this->assertTrue($service->send($this->actor, $prepared['token'])['duplicate']);
        Notification::assertCount(1);
    }

    public function test_invalid_filters_empty_audience_and_message_fail_before_campaign(): void
    {
        foreach ([['users' => [999]], ['user_types' => [99]], ['locales' => ['xx']], ['subject' => "a\nb"],
            ['line' => ''], ['users' => [3,4]]] as $data) {
            try { app(EmailCampaignService::class)->prepare($this->actor, $this->data($data)); $this->fail('Invalid campaign accepted'); }
            catch (ValidationException $exception) { $this->assertNotEmpty($exception->errors()); }
        }
        $this->assertDatabaseCount('admin_email_campaigns', 0); Notification::assertNothingSent();
    }

    public function test_missing_unsubscribe_text_is_explicit_and_unknown_locale_falls_back(): void
    {
        $user = User::find(1); $user->settings = [];
        $notification = new AdminMailNotification('Offer','Hello','Body','Goodbye');
        $this->assertStringContainsString('Отписаться', (string) $notification->toMail($user)->render());
        DB::table('user_messages')->delete();
        $this->expectException(ValidationException::class);
        $notification->toMail($user);
    }

    public function test_sync_transport_rejected_before_claim(): void
    {
        config(['admin_migration.bulk_email_enabled' => true, 'queue.default' => 'sync']);
        $service = app(EmailCampaignService::class);
        $prepared = $service->prepare($this->actor, $this->data());
        try { $service->send($this->actor, $prepared['token']); $this->fail('Sync accepted'); }
        catch (ValidationException $exception) { $this->assertNotEmpty($exception->errors()); }
        $this->assertSame('prepared', DB::table('admin_email_campaigns')->value('status'));
        Notification::assertNothingSent();
    }

    public function test_partial_queue_failure_is_not_replayed(): void
    {
        config(['admin_migration.bulk_email_enabled' => true, 'queue.default' => 'database']);
        $service = app(EmailCampaignService::class);
        $prepared = $service->prepare($this->actor, $this->data());
        $sender = \Mockery::mock(\Illuminate\Contracts\Notifications\Dispatcher::class);
        $sender->shouldReceive('send')->once()->ordered()->andReturnNull();
        $sender->shouldReceive('send')->once()->ordered()->andThrow(new \RuntimeException('Synthetic queue failure'));
        $this->app->instance(\Illuminate\Contracts\Notifications\Dispatcher::class, $sender);
        $result = $service->send($this->actor, $prepared['token']);
        $this->assertSame('uncertain', $result['status']); $this->assertSame(1, $result['queued']);
        $this->assertSame(1, $result['not_attempted']);
        $this->assertTrue($service->send($this->actor, $prepared['token'])['duplicate']);
    }

    public function test_page_prepares_and_confirms_with_guard_and_invalidates_changed_draft(): void
    {
        $this->actingAs($this->actor, 'filament');
        Livewire::test(EmailSender::class)->set('data', $this->data(['users' => [1]]))
            ->call('prepare')->assertHasNoErrors()->assertSet('prepared.count', 1)
            ->assertSee('sandbox=""', false)->call('confirm')->assertSet('result.status', 'suppressed')
            ->set('data.subject', 'Changed')->assertSet('prepared', null);
        Notification::assertNothingSent();
    }

    public function test_permission_revocation_blocks_service_and_foreign_campaign_is_hidden(): void
    {
        $service = app(EmailCampaignService::class);
        $prepared = $service->prepare($this->actor, $this->data());
        $other = \Mockery::mock(User::class)->makePartial(); $other->id = 102;
        $other->shouldReceive('hasPermission')->andReturn(true);
        try { $service->send($other, $prepared['token']); $this->fail('Foreign campaign accepted'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) { $this->assertSame(404, $exception->getStatusCode()); }
        $reader = \Mockery::mock(User::class)->makePartial(); $reader->shouldReceive('hasPermission')->andReturn(false);
        try { $service->send($reader, $prepared['token']); $this->fail('Unauthorized send'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) { $this->assertSame(403, $exception->getStatusCode()); }
        $this->actingAs($reader, 'filament'); $this->assertFalse(EmailSender::canAccess());
        Notification::assertNothingSent();
    }

    public function test_personal_entry_and_revoked_livewire_action(): void
    {
        $this->actingAs($this->actor, 'filament');
        $page = Livewire::withQueryParams(['id' => 1])->test(EmailSender::class)
            ->assertSet('data.users', [1])->set('data.line', 'Fixture')->call('prepare')->assertSet('prepared.count', 1);
        $reader = \Mockery::mock(User::class)->makePartial(); $reader->id = 101;
        $reader->shouldReceive('hasPermission')->andReturn(false);
        $this->actingAs($reader, 'filament');
        $page->call('confirm')->assertForbidden();
        Notification::assertNothingSent();
    }

    public function test_expired_preview_and_interrupted_claim_cannot_send(): void
    {
        config(['admin_migration.bulk_email_enabled' => true, 'queue.default' => 'database']);
        $service = app(EmailCampaignService::class);
        $prepared = $service->prepare($this->actor, $this->data());
        DB::table('admin_email_campaigns')->update(['created_at' => now()->subHour()]);
        try { $service->send($this->actor, $prepared['token']); $this->fail('Expired accepted'); }
        catch (ValidationException $exception) { $this->assertNotEmpty($exception->errors()); }
        DB::table('admin_email_campaigns')->update(['status' => 'processing']);
        $result = $service->send($this->actor, $prepared['token']);
        $this->assertSame('processing', $result['status']); $this->assertTrue($result['duplicate']);
        Notification::assertNothingSent();
    }

    public function test_worker_guard_rechecks_bulk_flag_subscription_and_address_only_for_campaigns(): void
    {
        $user = User::find(1);
        $notification = new AdminMailNotification('Offer','Hello','Body','Goodbye');
        $this->assertTrue($notification->shouldSend($user, 'mail'));
        $notification->campaignRecipientEmail = $user->email;
        $this->assertFalse($notification->shouldSend($user, 'mail'));
        config(['admin_migration.bulk_email_enabled' => true]);
        $this->assertTrue($notification->shouldSend($user, 'mail'));
        $user->news = 'NO'; $this->assertFalse($notification->shouldSend($user, 'mail'));
        $user->news = 'YES'; $user->email = 'changed@example.invalid';
        $this->assertFalse($notification->shouldSend($user, 'mail'));
    }

    public function test_missing_text_after_preview_marks_error_without_queue_or_replay(): void
    {
        config(['admin_migration.bulk_email_enabled' => true, 'queue.default' => 'database']);
        $service = app(EmailCampaignService::class);
        $prepared = $service->prepare($this->actor, $this->data());
        DB::table('user_messages')->delete();
        $this->assertSame('error', $service->send($this->actor, $prepared['token'])['status']);
        $this->assertTrue($service->send($this->actor, $prepared['token'])['duplicate']);
        Notification::assertNothingSent();
    }

    public function test_audience_limit_rejects_campaign_and_migration_rollback_preserves_users(): void
    {
        $rows = [];
        for ($id = 10; $id <= 1010; $id++) {
            $rows[] = ['id' => $id, 'email' => 'fixture'.$id.'@example.invalid', 'news' => 'YES',
                'type_id' => 1, 'settings' => '{"locale":"ru"}'];
        }
        foreach (array_chunk($rows, 100) as $chunk) { DB::table('users')->insert($chunk); }
        try { app(EmailCampaignService::class)->prepare($this->actor, $this->data()); $this->fail('Oversized audience accepted'); }
        catch (ValidationException $exception) { $this->assertNotEmpty($exception->errors()); }
        $this->assertDatabaseCount('admin_email_campaigns', 0);
        (require database_path('migrations/2026_10_08_230000_create_admin_email_campaigns_table.php'))->down();
        $this->assertDatabaseCount('users', 1006);
        $this->assertFalse(Schema::hasTable('admin_email_campaigns'));
        Notification::assertNothingSent();
    }
}
