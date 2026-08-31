<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Orders\Tables\OrderSaChatActions;
use App\Livewire\Admin\OrderSaChatHistory;
use App\Models\Orders;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SaConversation;
use App\Models\SaMessage;
use App\Models\User;
use App\Services\Admin\OrderSaChatService;
use App\Services\SynvolveWebhookService;
use App\Support\Admin\SaChatAttachment;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\CreatesSaChatSchema;
use Tests\TestCase;

class OrderSaChatTest extends TestCase
{
    use CreatesSaChatSchema;

    private Orders $order;

    private User $editor;

    private SaConversation $conversation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSaChatSchema();
        $this->mock(SynvolveWebhookService::class, fn ($mock) => $mock->shouldNotReceive('notifyManagerMessageForOrderOrPhone', 'notifyBotStatusForOrder'));
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
        $this->order = Orders::query()->forceCreate(['sa_bot_mode' => 'active']);
        $this->conversation = SaConversation::query()->create([
            'conversation_id' => 'CONV-TEST', 'orders_id' => $this->order->id,
            'unread_for_manager' => 1, 'bot_mode' => 'paused',
        ]);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        view()->addNamespace('admin-tests', base_path('tests/Fixtures/views'));
        config(['admin_migration.order_media_base_url' => 'https://viarcanvas.com']);
    }

    public function test_read_preserves_delivery_statuses_client_flags_and_dates_and_is_idempotent(): void
    {
        $message = $this->message(['status' => 'delivered']);
        $other = SaConversation::query()->create(['conversation_id' => 'OTHER', 'orders_id' => $this->order->id, 'unread_for_manager' => 1]);
        DB::table('order_user_comments')->insert(['order_id' => $this->order->id, 'comment' => 'Separate stream', 'admin_is_read' => 0]);
        $service = app(OrderSaChatService::class);
        $token = $this->token();
        $this->assertSame(1, $service->markAsRead($this->order, $this->editor, $token));
        $this->assertSame(0, $service->markAsRead($this->order, $this->editor, $token));
        $this->assertFalse($this->conversation->fresh()->unread_for_manager);
        $this->assertTrue($other->fresh()->unread_for_manager);
        $this->assertSame($message->getAttributes(), $message->fresh()->getAttributes());
        $this->assertEquals($this->conversation->updated_at, $this->conversation->fresh()->updated_at);
        $this->assertDatabaseHas('order_user_comments', ['admin_is_read' => 0]);
        Mail::assertNothingSent();
    }

    public function test_new_message_after_history_open_rejects_stale_read(): void
    {
        $this->message();
        $token = $this->token();
        $this->message(['message_id' => 'NEW-MESSAGE']);
        try {
            app(OrderSaChatService::class)->markAsRead($this->order, $this->editor, $token);
            $this->fail('Stale acknowledgement accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('snapshot', $exception->errors());
        }
        $this->assertTrue($this->conversation->fresh()->unread_for_manager);
    }

    public function test_tampered_snapshot_cannot_acknowledge_any_conversation(): void
    {
        try {
            app(OrderSaChatService::class)->markAsRead($this->order, $this->editor, 'forged');
            $this->fail('Tampered token accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('snapshot', $exception->errors());
        }
        $this->assertTrue($this->conversation->fresh()->unread_for_manager);
    }

    public function test_other_order_or_rebound_conversation_returns_403(): void
    {
        $token = $this->token();
        $foreign = Orders::query()->create();
        foreach ([$foreign, $this->order] as $target) {
            $this->conversation->update(['orders_id' => $foreign->id]);
            try {
                app(OrderSaChatService::class)->markAsRead($target, $this->editor, $token);
                $this->fail('Cross-order read accepted.');
            } catch (HttpException $exception) {
                $this->assertSame(403, $exception->getStatusCode());
            }
        }
    }

    public function test_reader_and_ordinary_user_cannot_mutate(): void
    {
        foreach ([$this->user(['browse_admin', 'read_orders']), $this->user([])] as $user) {
            try {
                app(OrderSaChatService::class)->markAsRead($this->order, $user, $this->token());
                $this->fail('Unauthorized read accepted.');
            } catch (AuthorizationException $exception) {
                $this->assertSame(403, $exception->status() ?? 403);
            }
        }
        $this->assertTrue($this->conversation->fresh()->unread_for_manager);
    }

    public function test_livewire_history_preserves_orphans_senders_status_and_safe_attachments_without_writes(): void
    {
        $this->message(['text' => '<script>unsafe</script>', 'attachments_json' => json_encode([
            ['local_path' => 'storage/sa/photo.jpg', 'name' => 'Фото'], ['path' => 'sa/document.pdf', 'name' => 'PDF'],
            ['status' => 'rejected', 'source_url' => 'https://untrusted.invalid/rejected', 'name' => 'Rejected'],
        ])]);
        $this->message(['message_id' => 'BOT', 'direction' => 'outbound', 'from_json' => '{"type":"bot"}', 'status' => 'delivered']);
        $this->message(['message_id' => 'SYSTEM', 'direction' => 'outbound', 'from_json' => '{"type":"system"}']);
        $this->message(['message_id' => 'MANAGER', 'direction' => 'outbound', 'from_json' => '{"type":"manager"}']);
        $this->message(['message_id' => 'ORPHAN', 'conversation_id' => 'MISSING', 'text' => 'История без диалога']);
        $this->actingAs($this->editor, 'filament');
        $table = Livewire::test(SaChatTestTable::class)->mountAction(TestAction::make('viewSaChat')->table($this->order));
        $html = $table->instance()->getMountedAction()->getModalContent()->render();
        foreach (['Клиент', 'Бот', 'Система', 'Менеджер', 'PAUSED', 'Доставлено', 'История без диалога', '&lt;script&gt;unsafe&lt;/script&gt;', 'https://viarcanvas.com/storage/sa/photo.jpg', 'https://viarcanvas.com/storage/sa/document.pdf'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        $this->assertStringNotContainsString('<script>unsafe</script>', $html);
        $this->assertStringNotContainsString('https://untrusted.invalid', $html);
        $this->assertTrue($this->conversation->fresh()->unread_for_manager);
        $this->assertSame('delivered', SaMessage::query()->where('message_id', 'BOT')->value('status'));
    }

    public function test_nested_livewire_read_and_reader_restrictions(): void
    {
        $this->message();
        $this->actingAs($this->editor, 'filament');
        Livewire::test(SaChatTestTable::class)->mountAction(TestAction::make('viewSaChat')->table($this->order))
            ->call('mountTableAction', 'readSaConversation', (string) $this->order->id, ['snapshot' => $this->token()])
            ->assertHasNoErrors();
        $this->assertFalse($this->conversation->fresh()->unread_for_manager);
        $this->conversation->refresh()->update(['unread_for_manager' => 1]);
        $this->actingAs($this->user(['browse_admin', 'read_orders']), 'filament');
        $table = Livewire::test(SaChatTestTable::class)->mountAction(TestAction::make('viewSaChat')->table($this->order));
        $this->assertStringNotContainsString('Отметить диалог прочитанным', $table->instance()->getMountedAction()->getModalContent()->render());
        Livewire::test(SaChatTestTable::class)->assertActionHidden(TestAction::make('readSaConversation')->table($this->order))
            ->call('mountTableAction', 'readSaConversation', (string) $this->order->id, ['snapshot' => $this->token()]);
        $this->assertTrue($this->conversation->fresh()->unread_for_manager);
        $this->actingAs($this->user([]), 'filament');
        Livewire::test(SaChatTestTable::class)->assertActionHidden(TestAction::make('viewSaChat')->table($this->order));
    }

    #[DataProvider('attachments')]
    public function test_attachment_paths_and_types(mixed $input, ?string $url, bool $image): void
    {
        $result = SaChatAttachment::describe($input);
        $this->assertSame($url, $result['url']);
        $this->assertSame($image, $result['image']);
    }

    public static function attachments(): array
    {
        return [
            [['local_path' => 'storage/sa/a.jpg'], 'https://viarcanvas.com/storage/sa/a.jpg', true],
            [['path' => 'sa/a.pdf'], 'https://viarcanvas.com/storage/sa/a.pdf', false],
            [['local_path' => 'storage/sa/a.mp3'], 'https://viarcanvas.com/storage/sa/a.mp3', false],
            [['path' => '../.env'], null, false], [['path' => 'javascript:alert(1)'], null, false],
            [['path' => '//example.com/a.jpg'], null, false], [['path' => 'sa/%2e%2e/a.jpg'], null, false],
            [['status' => 'rejected', 'local_path' => 'storage/sa/a.jpg'], null, false], ['malformed', null, false],
        ];
    }

    public function test_poll_refreshes_new_messages_status_mode_and_unread_without_writes(): void
    {
        $message = $this->message(['text' => 'Before refresh', 'status' => 'sent']);
        $this->actingAs($this->editor, 'filament');
        $history = Livewire::test(OrderSaChatHistory::class, ['orderId' => $this->order->id])
            ->assertSee('Before refresh')->assertSee('PAUSED')->assertSee('wire:poll.5s.visible', false);
        $message->update(['status' => 'delivered']);
        $this->message(['text' => 'New inbound']);
        $this->message(['text' => 'Other order secret', 'orders_id' => $this->order->id + 1]);
        $this->conversation->update(['bot_mode' => 'active']);
        $before = $this->conversation->fresh()->getAttributes();
        $history->call('$refresh')->assertSee('New inbound')->assertSee('Доставлено')
            ->assertSee('ACTIVE')->assertSee('Не прочитан менеджером')->assertDontSee('Other order secret');
        $this->assertSame($before, $this->conversation->fresh()->getAttributes());
        $this->assertSame('delivered', $message->fresh()->status);
        $this->assertDatabaseCount('sa_events', 0);
        $this->assertDatabaseCount('order_user_comments', 0);
        Http::assertNothingSent();
        Mail::assertNothingSent();
    }

    public function test_poll_keeps_the_existing_composer_instance(): void
    {
        $this->actingAs($this->editor, 'filament');
        $history = Livewire::test(OrderSaChatHistory::class, ['orderId' => $this->order->id]);
        $dom = new \DOMDocument;
        @$dom->loadHTML($history->html());
        $composer = (new \DOMXPath($dom))->query('//*[@data-chat-composer="sa"]')->item(0);
        $this->assertNotNull($composer);
        $id = $composer->getAttribute('wire:id');
        $this->assertNotSame('', $id);
        $this->message(['text' => 'Refresh without remount']);
        $history->call('$refresh')->assertSee('Refresh without remount');
        $this->assertStringContainsString('wire:id="'.$id.'"', $history->html());
        $this->assertStringNotContainsString('@js($snapshot)', $history->html());
    }

    public function test_poll_reader_can_view_but_cannot_acknowledge_and_revocation_blocks_refresh(): void
    {
        $this->actingAs($this->user(['browse_admin', 'read_orders']), 'filament');
        $history = Livewire::test(OrderSaChatHistory::class, ['orderId' => $this->order->id])
            ->assertSee('PAUSED')->assertDontSee('Отметить диалог прочитанным')->assertDontSee('Ответить клиенту в WhatsApp');
        $history->call('acknowledge', $this->token())->assertForbidden();
        $this->assertTrue($this->conversation->fresh()->unread_for_manager);
        $this->actingAs($this->editor, 'filament');
        $history = Livewire::test(OrderSaChatHistory::class, ['orderId' => $this->order->id]);
        $this->actingAs($this->user([]), 'filament');
        $history->call('$refresh')->assertForbidden();
        Livewire::test(OrderSaChatHistory::class, ['orderId' => $this->order->id])->assertForbidden();
    }

    public function test_history_acknowledgement_rejects_stale_token_then_updates_only_unread(): void
    {
        $this->actingAs($this->editor, 'filament');
        $this->message();
        $stale = $this->token();
        $message = $this->message(['text' => 'Later message', 'status' => 'delivered']);
        $history = Livewire::test(OrderSaChatHistory::class, ['orderId' => $this->order->id]);
        $history->call('acknowledge', $stale)->assertNotDispatched('order-sa-read');
        $this->assertTrue($this->conversation->fresh()->unread_for_manager);
        $history->call('acknowledge', $this->token())->assertHasNoErrors()
            ->assertDispatched('order-sa-read', orderId: $this->order->id)->assertSee('Прочитан менеджером');
        $this->assertFalse($this->conversation->fresh()->unread_for_manager);
        $this->assertSame('delivered', $message->fresh()->status);
        Http::assertNothingSent();
        Mail::assertNothingSent();
    }

    public function test_poll_order_id_cannot_be_changed(): void
    {
        $this->actingAs($this->editor, 'filament');
        $history = Livewire::test(OrderSaChatHistory::class, ['orderId' => $this->order->id]);
        $this->expectException(CannotUpdateLockedPropertyException::class);
        $history->set('orderId', $this->order->id + 1);
    }

    private function message(array $attributes = []): SaMessage
    {
        return SaMessage::query()->create(array_merge(['orders_id' => $this->order->id,
            'conversation_id' => $this->conversation->conversation_id, 'message_id' => 'MSG-'.SaMessage::query()->count(),
            'direction' => 'inbound', 'text' => 'Текст', 'status' => 'received', 'sent_at' => '2026-01-01 12:00:00'], $attributes))->fresh();
    }

    private function token(): string
    {
        return app(OrderSaChatService::class)->readToken($this->order->load('saMessages'), $this->conversation->fresh());
    }

    private function user(array $permissions): User
    {
        $role = Role::query()->create(['name' => 'sa-chat-'.Role::query()->count()]);
        foreach ($permissions as $key) {
            $role->permissions()->attach(Permission::query()->firstOrCreate(['key' => $key]));
        }
        $id = DB::table('users')->insertGetId(['role_id' => $role->id, 'email' => 'sa-chat-'.$role->id.'@example.invalid']);

        return User::query()->findOrFail($id);
    }
}

class SaChatTestTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table->query(Orders::query())->columns([TextColumn::make('id')])
            ->recordActions([OrderSaChatActions::history(), OrderSaChatActions::read()]);
    }

    public function render()
    {
        return view('admin-tests::client-chat-table');
    }
}
