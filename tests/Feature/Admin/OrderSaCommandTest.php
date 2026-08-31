<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Orders\Tables\OrderSaChatActions;
use App\Models\Orders;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SaConversation;
use App\Models\SaMessage;
use App\Models\User;
use App\Services\Admin\OrderSaCommandService;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\CreatesSaChatSchema;
use Tests\TestCase;

class OrderSaCommandTest extends TestCase
{
    use CreatesSaChatSchema;

    private Orders $order;

    private User $editor;

    private SaConversation $conversation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSaChatSchema();
        config(['admin_migration.sa_commands_enabled' => false,
            'services.synvolve.manager_message_webhook_url' => 'https://sa.test.invalid/message',
            'services.synvolve.bot_status_webhook_url' => 'https://sa.test.invalid/bot']);
        foreach (['roles' => 'name', 'permissions' => 'key'] as $name => $field) {
            Schema::create($name, function (Blueprint $table) use ($field): void {
                $table->id();
                $table->string($field);
                $table->timestamps();
            });
        }
        Schema::create('permission_role', function (Blueprint $table): void {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('permission_id');
        });
        Schema::create('user_roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
        });
        $this->editor = $this->user(['browse_admin', 'browse_orders', 'read_orders', 'edit_orders']);
        $this->order = Orders::query()->forceCreate(['id' => 18451, 'user_id' => $this->editor->id,
            'sa_conversation_id' => 'CONV-TEST', 'sa_bot_mode' => 'paused',
            'delivery' => json_encode(['payer_phone' => '+12025550199'])]);
        $this->conversation = SaConversation::query()->create(['orders_id' => $this->order->id,
            'conversation_id' => 'CONV-TEST', 'client_phone' => '+12025550123', 'channel' => 'whatsapp',
            'bot_mode' => 'paused', 'unread_for_manager' => 1]);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        view()->addNamespace('admin-tests', base_path('tests/Fixtures/views'));
    }

    public function test_uat_send_and_bot_do_not_contact_anyone_or_change_client_chat_or_mode(): void
    {
        $result = $this->execute(['handoff' => true]);
        $this->assertSame('uat_suppressed', $result['status']);
        $this->assertDatabaseHas('sa_messages', ['status' => 'uat_suppressed', 'text' => 'Тест']);
        $this->assertDatabaseCount('sa_bot_controls', 1);
        $this->execute(['action' => 'resume_bot']);
        $this->assertSame('paused', $this->conversation->fresh()->bot_mode);
        $this->assertSame('paused', $this->order->fresh()->sa_bot_mode);
        $this->assertDatabaseCount('order_user_comments', 0);
        Http::assertNothingSent();
        Mail::assertNothingSent();
    }

    public function test_test_conversation_has_hard_transport_lock_even_when_enabled(): void
    {
        config(['admin_migration.sa_commands_enabled' => true]);
        $this->conversation->update(['conversation_id' => 'ADM-FIL-UAT-18451']);
        $this->assertSame('uat_suppressed', $this->execute()['status']);
        Http::assertNothingSent();
    }

    public function test_accepted_send_targets_conversation_phone_preserves_mode_and_mirrors_once(): void
    {
        config(['admin_migration.sa_commands_enabled' => true]);
        Http::fake(['*' => Http::response([], 202)]);
        $token = $this->token();
        $result = $this->execute(['token' => $token]);
        $this->assertSame('accepted', $result['status']);
        $this->assertTrue($this->execute(['token' => $token])['duplicate']);
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request->url() === 'https://sa.test.invalid/message'
            && $request['phone'] === '+12025550123' && $request['event'] === 'manager_message'
            && $request['context']['order_id'] === 18451 && $request['context']['conversation_id'] === 'CONV-TEST'
            && $request->hasHeader('Idempotency-Key'));
        $this->assertDatabaseHas('sa_messages', ['status' => 'queued']);
        $this->assertDatabaseCount('order_user_comments', 1);
        $this->assertDatabaseHas('order_user_comments', ['is_admin' => 1, 'is_read' => 0, 'admin_is_read' => 1, 'sa_direction' => 'outbound']);
        $this->assertSame('paused', $this->conversation->fresh()->bot_mode);
        $this->assertTrue($this->conversation->fresh()->unread_for_manager);
    }

    #[DataProvider('botActions')]
    public function test_bot_commands_sync_both_modes_only_after_acceptance(string $action, string $mode): void
    {
        config(['admin_migration.sa_commands_enabled' => true]);
        Http::fake(function ($request) {
            $this->assertSame('paused', $this->conversation->fresh()->bot_mode);
            $this->assertSame('paused', $this->order->fresh()->sa_bot_mode);

            return Http::response([], 200);
        });
        $token = $this->token();
        $this->assertSame('accepted', $this->execute(['token' => $token, 'action' => $action])['status']);
        $this->assertSame($mode, $this->conversation->fresh()->bot_mode);
        $this->assertSame($mode, $this->order->fresh()->sa_bot_mode);
        $this->assertTrue($this->execute(['token' => $token, 'action' => $action])['duplicate']);
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request['event'] === 'bot_status_changed' && $request['bot_status'] === $mode
            && $request['client_id'] === '18451' && $request['phone'] === '+12025550123');
        $this->assertDatabaseCount('sa_bot_controls', 1);
        $this->assertDatabaseCount('sa_messages', 0);
    }

    public static function botActions(): array
    {
        return [['pause_bot', 'paused'], ['resume_bot', 'active'], ['handoff_to_manager', 'handoff_to_manager']];
    }

    public function test_send_with_handoff_and_partial_failure_never_resends_text(): void
    {
        config(['admin_migration.sa_commands_enabled' => true]);
        Http::fake(['*/message' => Http::response([], 202), '*/bot' => Http::response([], 503)]);
        $token = $this->token();
        $this->assertSame('partial', $this->execute(['token' => $token, 'handoff' => true])['status']);
        $this->assertTrue($this->execute(['token' => $token, 'handoff' => true])['duplicate']);
        Http::assertSentCount(2);
        $this->assertSame('paused', $this->conversation->fresh()->bot_mode);
        $this->assertDatabaseCount('order_user_comments', 1);
        $this->assertDatabaseHas('sa_messages', ['status' => 'queued']);
    }

    public function test_timeout_is_uncertain_does_not_run_handoff_and_is_not_retried(): void
    {
        config(['admin_migration.sa_commands_enabled' => true]);
        $calls = 0;
        Http::fake(function () use (&$calls) {
            $calls++;
            throw new ConnectionException('Simulated timeout');
        });
        $token = $this->token();
        $this->assertSame('uncertain', $this->execute(['token' => $token, 'handoff' => true])['status']);
        $this->assertTrue($this->execute(['token' => $token, 'handoff' => true])['duplicate']);
        $this->assertSame(1, $calls);
        $this->assertDatabaseHas('sa_messages', ['status' => 'delivery_unknown']);
        $this->assertDatabaseCount('order_user_comments', 0);
        $this->assertSame('paused', $this->conversation->fresh()->bot_mode);
    }

    public function test_accepted_handoff_after_message_and_expired_token_validation(): void
    {
        config(['admin_migration.sa_commands_enabled' => true]);
        Http::fake(['*' => Http::response([], 202)]);
        $this->assertSame('accepted', $this->execute(['handoff' => true])['status']);
        Http::assertSentCount(2);
        $this->assertSame('handoff_to_manager', $this->conversation->fresh()->bot_mode);
        $this->assertSame('handoff_to_manager', $this->order->fresh()->sa_bot_mode);
        $token = $this->token();
        $this->travel(61)->minutes();
        try {
            $this->execute(['token' => $token]);
            $this->fail('Expired form accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('token', $exception->errors());
        }
        $this->assertDatabaseCount('sa_events', 1);
        Http::assertSentCount(2);
    }

    public function test_delivery_callback_is_not_downgraded_and_mirror_is_not_duplicated(): void
    {
        config(['admin_migration.sa_commands_enabled' => true]);
        Http::fake(function ($request) {
            $messageId = $request['context']['message_id'];
            SaMessage::query()->where('message_id', $messageId)->update(['status' => 'delivered']);
            DB::table('order_user_comments')->insert(['order_id' => 18451, 'comment' => 'Тест', 'sa_message_id' => $messageId]);

            return Http::response([], 200);
        });
        $this->execute();
        $this->assertDatabaseHas('sa_messages', ['status' => 'delivered']);
        $this->assertDatabaseCount('order_user_comments', 1);
    }

    public function test_concurrent_mode_change_is_not_overwritten(): void
    {
        config(['admin_migration.sa_commands_enabled' => true]);
        Http::fake(function () {
            $this->conversation->update(['bot_mode' => 'handoff_to_manager']);

            return Http::response([], 200);
        });
        $this->assertSame('state_conflict', $this->execute(['action' => 'resume_bot'])['status']);
        $this->assertSame('handoff_to_manager', $this->conversation->fresh()->bot_mode);
    }

    #[DataProvider('invalidInputs')]
    public function test_validation_has_no_partial_writes(array $input): void
    {
        try {
            $this->execute($input);
            $this->fail('Invalid command accepted.');
        } catch (ValidationException $exception) {
            $this->assertNotEmpty($exception->errors());
        }
        $this->assertDatabaseCount('sa_events', 0);
        $this->assertDatabaseCount('sa_messages', 0);
        Http::assertNothingSent();
    }

    public static function invalidInputs(): array
    {
        return [[['text' => '   ']], [['text' => str_repeat('a', 10001)]], [['action' => 'delete']], [['token' => 'tampered']]];
    }

    public function test_stale_phone_and_duplicate_with_changed_content_are_rejected(): void
    {
        $token = $this->token();
        $this->conversation->update(['client_phone' => '+12025550124']);
        try {
            $this->execute(['token' => $token]);
            $this->fail('Stale recipient accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('token', $exception->errors());
        }
        $token = $this->token();
        $this->execute(['token' => $token]);
        try {
            $this->execute(['token' => $token, 'text' => 'Другой текст']);
            $this->fail('Token reused for new text.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('token', $exception->errors());
        }
        $this->assertDatabaseCount('sa_events', 1);
    }

    public function test_missing_whatsapp_phone_or_channel_is_not_replaced_by_fallback(): void
    {
        foreach ([['client_phone' => '+0000000'], ['client_phone' => '+12025550123', 'channel' => 'telegram']] as $change) {
            $this->conversation->update($change);
            try {
                $this->execute();
                $this->fail('Invalid recipient accepted.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('token', $exception->errors());
            }
        }
        $this->assertDatabaseCount('sa_events', 0);
    }

    public function test_service_permissions_cross_order_and_token_author_are_enforced(): void
    {
        $service = app(OrderSaCommandService::class);
        foreach ([$this->user(['read_orders']), $this->user([])] as $user) {
            try {
                $service->execute($this->order, $user, ['token' => $this->token(), 'action' => 'send', 'text' => 'Denied']);
                $this->fail('Unauthorized write.');
            } catch (AuthorizationException $exception) {
                $this->assertSame(403, $exception->status() ?? 403);
            }
        }
        $token = $this->token();
        foreach ([[Orders::query()->create(), $this->editor], [$this->order, $this->user(['edit_orders'])]] as [$order, $user]) {
            try {
                $service->execute($order, $user, ['token' => $token, 'action' => 'send', 'text' => 'Denied']);
                $this->fail('Foreign token accepted.');
            } catch (HttpException $exception) {
                $this->assertSame(403, $exception->getStatusCode());
            }
        }
        $this->assertDatabaseCount('sa_events', 0);
    }

    public function test_livewire_nested_send_and_bot_forms_with_reader_denial(): void
    {
        $this->actingAs($this->editor, 'filament');
        Livewire::test(SaCommandsTestTable::class)->mountAction(TestAction::make('viewSaChat')->table($this->order))
            ->call('mountTableAction', 'sendSaMessage', (string) $this->order->id, ['conversation_id' => $this->conversation->id])
            ->fillForm(['text' => 'Тест Livewire', 'handoff' => true])->callMountedAction()->assertHasNoErrors();
        Livewire::test(SaCommandsTestTable::class)->mountAction(TestAction::make('viewSaChat')->table($this->order))
            ->call('mountTableAction', 'controlSaBot', (string) $this->order->id, ['conversation_id' => $this->conversation->id])
            ->fillForm(['action' => 'resume_bot'])->callMountedAction()->assertHasNoErrors();
        $this->assertDatabaseCount('sa_events', 2);
        $this->assertDatabaseHas('sa_messages', ['text' => 'Тест Livewire', 'status' => 'uat_suppressed']);
        $this->actingAs($this->user(['browse_admin', 'read_orders']), 'filament');
        Livewire::test(SaCommandsTestTable::class)
            ->assertActionHidden(TestAction::make('sendSaMessage')->table($this->order))
            ->assertActionHidden(TestAction::make('controlSaBot')->table($this->order))
            ->call('mountTableAction', 'sendSaMessage', (string) $this->order->id, ['conversation_id' => $this->conversation->id]);
        $this->assertDatabaseCount('sa_events', 2);
        Http::assertNothingSent();
        Mail::assertNothingSent();
    }

    public function test_livewire_expired_form_shows_visible_warning_without_dispatch(): void
    {
        $this->actingAs($this->editor, 'filament');
        $component = Livewire::test(SaCommandsTestTable::class)
            ->call('mountTableAction', 'controlSaBot', (string) $this->order->id, ['conversation_id' => $this->conversation->id])
            ->fillForm(['action' => 'resume_bot']);
        $this->travel(61)->minutes();
        $component->callMountedAction()->assertHasNoErrors()->assertNotified('Команда не выполнена');
        $this->assertDatabaseCount('sa_events', 0);
        $this->assertSame('paused', $this->conversation->fresh()->bot_mode);
        Http::assertNothingSent();
        Mail::assertNothingSent();
    }

    private function execute(array $input = []): array
    {
        return app(OrderSaCommandService::class)->execute($this->order, $this->editor,
            array_merge(['token' => $input['token'] ?? $this->token(), 'action' => 'send', 'text' => 'Тест', 'handoff' => false], $input));
    }

    private function token(): string
    {
        return app(OrderSaCommandService::class)->token($this->order, $this->editor, $this->conversation->id);
    }

    private function user(array $permissions): User
    {
        $role = Role::query()->create(['name' => 'sa-command-'.Role::query()->count()]);
        foreach ($permissions as $key) {
            $role->permissions()->attach(Permission::query()->firstOrCreate(['key' => $key]));
        }

        return User::query()->findOrFail(DB::table('users')->insertGetId(['role_id' => $role->id, 'email' => 'test-'.$role->id.'@example.invalid']));
    }
}

class SaCommandsTestTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table->query(Orders::query())->columns([TextColumn::make('id')])
            ->recordActions([OrderSaChatActions::history(), OrderSaChatActions::read(), OrderSaChatActions::reply(), OrderSaChatActions::bot()]);
    }

    public function render()
    {
        return view('admin-tests::client-chat-table');
    }
}
