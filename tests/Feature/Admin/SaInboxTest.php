<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\SaConversations\Pages\ListSaConversations;
use App\Filament\Resources\SaConversations\Pages\ViewSaConversation;
use App\Models\Orders;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SaConversation;
use App\Models\SaMessage;
use App\Models\User;
use App\Services\Admin\SaInboxService;
use App\Services\Admin\OrderSaCommandService;
use Filament\Facades\Filament;
use Filament\Actions\Testing\TestAction;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\Support\CreatesSaChatSchema;
use Tests\TestCase;

class SaInboxTest extends TestCase
{
    use CreatesSaChatSchema;
    private User $editor;
    private SaConversation $conversation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSaChatSchema();
        config(['admin_migration.sa_commands_enabled' => false]);
        foreach (['roles' => 'name', 'permissions' => 'key'] as $name => $field) {
            Schema::create($name, function (Blueprint $table) use ($field) {
                $table->id(); $table->string($field); $table->timestamps();
            });
        }
        Schema::create('permission_role', function (Blueprint $table) { $table->integer('role_id'); $table->integer('permission_id'); });
        Schema::create('user_roles', function (Blueprint $table) { $table->integer('user_id'); $table->integer('role_id'); });
        $this->editor = $this->user(['browse_admin', 'browse_orders', 'read_orders', 'edit_orders', 'add_orders']);
        $this->conversation = SaConversation::create(['conversation_id' => 'INBOX-TEST', 'channel' => 'whatsapp',
            'client_phone' => '+12025550123', 'client_name' => 'Inbox client', 'bot_mode' => 'paused', 'unread_for_manager' => true]);
        $this->actingAs($this->editor, 'filament');
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_list_filters_search_and_card_preserve_unread(): void
    {
        $this->message('M-1');
        $other = SaConversation::create(['conversation_id' => 'OTHER', 'client_name' => 'Other', 'unread_for_manager' => false, 'orders_id' => 99]);
        Livewire::test(ListSaConversations::class)->assertCanSeeTableRecords([$this->conversation, $other])
            ->filterTable('scope', 'unread')->assertCanSeeTableRecords([$this->conversation])->assertCanNotSeeTableRecords([$other])
            ->resetTableFilters()->searchTable('Inbox client')->assertCanSeeTableRecords([$this->conversation])->assertCanNotSeeTableRecords([$other]);
        Livewire::test(ViewSaConversation::class, ['record' => $this->conversation->id])->assertSee('Incoming text');
        $this->assertTrue($this->conversation->fresh()->unread_for_manager);
        Http::assertNothingSent(); Mail::assertNothingSent();
    }

    public function test_bind_mirrors_once_and_rejects_conflicting_order(): void
    {
        $order = $this->order();
        $this->message('M-1');
        $this->message('M-2', ['direction' => 'outbound', 'status' => 'uat_suppressed']);
        $service = app(SaInboxService::class);
        $service->bind($this->conversation, $this->editor, $order->id);
        $service->bind($this->conversation->fresh(), $this->editor, $order->id);
        $this->assertDatabaseCount('order_user_comments', 1);
        $this->assertDatabaseHas('order_user_comments', ['order_id' => $order->id, 'sa_message_id' => 'M-1', 'admin_is_read' => 0]);
        $this->assertDatabaseHas('sa_messages', ['message_id' => 'M-2', 'orders_id' => $order->id]);
        $this->assertTrue($this->conversation->fresh()->unread_for_manager);
        $this->assertSame('paused', $order->fresh()->sa_bot_mode);
        $this->expectException(ValidationException::class);
        $service->bind($this->conversation->fresh(), $this->editor, $this->order()->id);
    }

    public function test_stale_read_snapshot_preserves_new_messages(): void
    {
        $service = app(SaInboxService::class);
        $token = $service->readToken($this->conversation);
        $this->message('NEW');
        try { $service->acknowledge($this->conversation, $this->editor, $token); $this->fail('Stale read accepted'); }
        catch (ValidationException $exception) { $this->assertArrayHasKey('snapshot', $exception->errors()); }
        $this->assertTrue($this->conversation->fresh()->unread_for_manager);
        $service->acknowledge($this->conversation, $this->editor, $service->readToken($this->conversation->fresh()));
        $this->assertFalse($this->conversation->fresh()->unread_for_manager);
        $this->assertSame('received', SaMessage::first()->status);
    }

    public function test_create_order_is_atomic_and_idempotent(): void
    {
        // Existing contact avoids unrelated registration notifications in this fixture.
        DB::table('users')->where('id', $this->editor->id)->update(['phone' => $this->conversation->client_phone]);
        $this->message('M-1');
        $service = app(SaInboxService::class);
        $order = $service->createOrder($this->conversation, $this->editor);
        $again = $service->createOrder($this->conversation->fresh(), $this->editor);
        $this->assertSame($order->id, $again->id);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_user_comments', 1);
        $this->assertSame('watching', $order->status);
        $this->assertSame('paused', $order->sa_bot_mode);
        $this->assertSame((int) $order->id, (int) $this->conversation->fresh()->orders_id);
        Http::assertNothingSent(); Mail::assertNothingSent();
    }

    public function test_standalone_commands_use_uat_guard_and_deduplicate(): void
    {
        $service = app(OrderSaCommandService::class);
        $data = ['token' => $service->inboxToken($this->conversation, $this->editor), 'action' => 'send', 'text' => 'Hello'];
        $result = $service->executeInbox($this->conversation, $this->editor, $data);
        $again = $service->executeInbox($this->conversation, $this->editor, $data);
        $this->assertSame('uat_suppressed', $result['status']);
        $this->assertTrue($again['duplicate']);
        $this->assertDatabaseCount('sa_messages', 1);
        $this->assertDatabaseCount('order_user_comments', 0);
        $this->assertTrue($this->conversation->fresh()->unread_for_manager);
        $service->executeInbox($this->conversation, $this->editor, ['token' => $service->inboxToken($this->conversation, $this->editor), 'action' => 'resume_bot']);
        $this->assertSame('paused', $this->conversation->fresh()->bot_mode);
        Http::assertNothingSent(); Mail::assertNothingSent();
    }

    public function test_reply_modal_token_reaches_command_service(): void
    {
        Livewire::test(ViewSaConversation::class, ['record' => $this->conversation->id])
            ->callAction('reply', data: ['text' => 'From modal', 'handoff' => false])->assertHasNoErrors();
        $this->assertDatabaseHas('sa_messages', ['text' => 'From modal', 'status' => 'uat_suppressed']);
        Http::assertNothingSent();
    }

    public function test_read_only_user_cannot_bind_or_send(): void
    {
        $reader = $this->user(['browse_admin', 'browse_orders', 'read_orders']);
        $this->actingAs($reader, 'filament');
        Livewire::test(ViewSaConversation::class, ['record' => $this->conversation->id])->assertActionHidden('bind')->assertActionHidden('reply');
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(SaInboxService::class)->bind($this->conversation, $reader, $this->order()->id);
    }

    public function test_accepted_standalone_send_and_bot_use_conversation_phone(): void
    {
        config(['admin_migration.sa_commands_enabled' => true, 'services.synvolve.manager_message_webhook_url' => 'https://sa.test.invalid/message',
            'services.synvolve.bot_status_webhook_url' => 'https://sa.test.invalid/bot']);
        Http::fake(['sa.test.invalid/*' => Http::response([], 200)]);
        $service = app(OrderSaCommandService::class);
        $service->executeInbox($this->conversation, $this->editor, ['token' => $service->inboxToken($this->conversation, $this->editor), 'action' => 'send', 'text' => 'Accepted']);
        $result = $service->executeInbox($this->conversation, $this->editor, ['token' => $service->inboxToken($this->conversation->fresh(), $this->editor), 'action' => 'resume_bot']);
        $this->assertSame('accepted', $result['status']);
        $this->assertSame('active', $this->conversation->fresh()->bot_mode);
        $this->assertDatabaseCount('order_user_comments', 0);
        Http::assertSent(fn ($request) => $request['event'] === 'bot_status_changed' && $request['client_id'] === 'INBOX-TEST' && $request['phone'] === '+12025550123');
    }

    public function test_invalid_create_rolls_back_and_order_conflict_cannot_bind(): void
    {
        $this->conversation->update(['client_phone' => 'invalid']);
        try { app(SaInboxService::class)->createOrder($this->conversation, $this->editor); $this->fail('Invalid create accepted'); }
        catch (ValidationException $exception) { $this->assertArrayHasKey('conversation', $exception->errors()); }
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('sa_events', 0);
        $this->assertNull($this->conversation->fresh()->orders_id);
        $order = $this->order();
        $order->forceFill(['sa_conversation_id' => 'OTHER-CONVERSATION'])->save();
        try { app(SaInboxService::class)->bind($this->conversation, $this->editor, $order->id); $this->fail('Conflict accepted'); }
        catch (ValidationException $exception) { $this->assertArrayHasKey('order_id', $exception->errors()); }
        $this->assertNull($this->conversation->fresh()->orders_id);
        Http::assertNothingSent(); Mail::assertNothingSent();
    }

    public function test_new_contact_create_and_badge_polling(): void
    {
        $order = app(SaInboxService::class)->createOrder($this->conversation, $this->editor);
        $this->assertDatabaseHas('users', ['id' => $order->user_id, 'phone' => '+12025550123', 'first_name' => 'Inbox']);
        $badge = Livewire::test(\App\Livewire\Admin\SaInboxBadge::class)->assertDispatched('sa-inbox-count', count: 1);
        $this->conversation->fresh()->update(['unread_for_manager' => false]);
        $badge->call('$refresh')->assertDispatched('sa-inbox-count', count: 0);
        $this->assertDatabaseCount('orders', 1);
        Http::assertNothingSent(); Mail::assertNothingSent();
    }

    public function test_token_cannot_target_another_conversation_and_revocation_denies_page(): void
    {
        $service = app(OrderSaCommandService::class);
        $token = $service->inboxToken($this->conversation, $this->editor);
        $other = SaConversation::create(['conversation_id' => 'OTHER', 'client_phone' => '+12025550123', 'channel' => 'whatsapp']);
        try { $service->executeInbox($other, $this->editor, ['token' => $token, 'action' => 'send', 'text' => 'No']); $this->fail('Wrong conversation accepted'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) { $this->assertSame(403, $exception->getStatusCode()); }
        $this->assertDatabaseCount('sa_events', 0);
        $list = Livewire::test(ListSaConversations::class);
        $this->actingAs($this->user(['browse_admin']), 'filament');
        $list->call('$refresh')->assertForbidden();
        Livewire::test(ListSaConversations::class)->assertForbidden();
        Livewire::test(\App\Livewire\Admin\SaInboxBadge::class)->assertDontSee('SA-диалоги, непрочитанных');
        Http::assertNothingSent();
    }

    private function user(array $permissions): User
    {
        $role = Role::create(['name' => 'inbox-'.Role::count()]);
        foreach ($permissions as $key) { $role->permissions()->attach(Permission::firstOrCreate(['key' => $key])); }
        return User::findOrFail(DB::table('users')->insertGetId(['role_id' => $role->id, 'email' => 'test-'.$role->id.'@example.invalid']));
    }
    private function order(): Orders { return Orders::forceCreate(['user_id' => $this->editor->id, 'status' => 'watching']); }
    private function message(string $id, array $attributes = []): SaMessage
    {
        return SaMessage::create($attributes + ['conversation_id' => $this->conversation->conversation_id, 'message_id' => $id, 'direction' => 'inbound', 'text' => 'Incoming text', 'status' => 'received']);
    }
}
