<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Orders\Tables\OrderPainterChatActions;
use App\Livewire\Admin\OrderChatComposer;
use App\Mail\AdminToPainterComment;
use App\Models\Orders;
use App\Models\OrdersChats;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Admin\OrderPainterChatService;
use App\Services\BestEffortMailService;
use App\Services\SynvolveWebhookService;
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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Livewire;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class OrderPainterChatTest extends TestCase
{
    private Orders $order;

    private User $editor;

    protected function setUp(): void
    {
        parent::setUp();
        config(['admin_migration.painter_chat_notifications_enabled' => false]);
        Mail::fake();
        $this->mock(SynvolveWebhookService::class, fn (MockInterface $mock) => $mock->shouldNotReceive('notifyManagerMessageForOrder'));
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
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->string('email');
            $table->text('settings')->nullable();
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status')->default('watching');
            $table->timestamps();
        });
        Schema::create('painter_orders', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
        // Match the existing table: deliberately no user_id or image FK.
        Schema::create('orders_chats', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('orders_id');
            $table->text('comment');
            foreach (['is_admin', 'is_read', 'admin_is_read', 'is_img_sketch', 'is_img_painter'] as $flag) {
                $table->integer($flag)->default(0);
            }
            $table->timestamps();
        });
        Schema::create('user_messages', function (Blueprint $table): void {
            $table->id();
            $table->string('admin_user_chat_title');
        });
        Schema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->string('table_name');
            $table->string('column_name');
            $table->unsignedBigInteger('foreign_key');
            $table->string('locale');
            $table->text('value');
        });
        $this->editor = $this->user(['browse_admin', 'browse_orders', 'read_orders', 'edit_orders']);
        $this->order = Orders::query()->forceCreate(['status' => 'completed']);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        view()->addNamespace('admin-tests', base_path('tests/Fixtures/views'));
    }

    public function test_general_reply_preserves_flags_even_without_assignment_or_on_closed_order(): void
    {
        $result = $this->send(['comment' => '  Ответ  ', 'is_admin' => 0, 'is_read' => 1, 'orders_id' => 999]);
        $this->assertTrue($result['notificationsSuppressed']);
        $this->assertDatabaseHas('orders_chats', [
            'orders_id' => $this->order->id, 'comment' => 'Ответ', 'is_admin' => 1,
            'is_read' => 0, 'admin_is_read' => 1, 'is_img_sketch' => 0, 'is_img_painter' => 0,
        ]);
        Mail::assertNothingSent();
    }

    #[DataProvider('invalidInputs')]
    public function test_invalid_payload_does_not_create_message(array $input): void
    {
        try {
            $this->send($input);
            $this->fail('Invalid payload accepted.');
        } catch (ValidationException $exception) {
            $this->assertNotEmpty($exception->errors());
        }
        $this->assertDatabaseCount('orders_chats', 0);
        Mail::assertNothingSent();
    }

    public static function invalidInputs(): array
    {
        return [
            [['comment' => '   ']], [['comment' => ['not text']]],
            [['comment' => str_repeat('a', 10001)]], [['image_type' => 'is_img_sketch']],
            [['image_id' => 1]], [['order_painter_image_id' => 1]],
        ];
    }

    public function test_read_is_explicit_idempotent_and_preserves_painter_state_and_dates(): void
    {
        $message = $this->incoming(['is_read' => 1]);
        $other = $this->incoming();
        $service = app(OrderPainterChatService::class);
        $this->assertSame(1, $service->markMessageAsRead($this->order, $this->editor, $message->id));
        $this->assertSame(0, $service->markMessageAsRead($this->order, $this->editor, $message->id));
        $this->assertSame(1, $message->fresh()->is_read);
        $this->assertEquals($message->updated_at, $message->fresh()->updated_at);
        $this->assertSame(0, $other->fresh()->admin_is_read);
        $this->assertSame(0, $service->markMessageAsRead($this->order, $this->editor, $this->send()['message']->id));
    }

    public function test_read_cannot_target_another_order(): void
    {
        $message = $this->incoming(['orders_id' => Orders::query()->create()->id]);
        try {
            app(OrderPainterChatService::class)->markMessageAsRead($this->order, $this->editor, $message->id);
            $this->fail('Cross-order mutation accepted.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        $this->assertSame(0, $message->fresh()->admin_is_read);
    }

    public function test_read_only_and_ordinary_users_cannot_mutate_chat(): void
    {
        $message = $this->incoming();
        foreach ([$this->user(['browse_admin', 'read_orders']), $this->user([])] as $user) {
            foreach (['send', 'read'] as $operation) {
                try {
                    $service = app(OrderPainterChatService::class);
                    $operation === 'send' ? $service->send($this->order, $user, ['comment' => 'Denied'])
                        : $service->markMessageAsRead($this->order, $user, $message->id);
                    $this->fail('Unauthorized mutation accepted.');
                } catch (AuthorizationException $exception) {
                    $this->assertSame(403, $exception->status() ?? 403);
                }
            }
        }
        $this->assertDatabaseCount('orders_chats', 1);
        $this->assertSame(0, $message->fresh()->admin_is_read);
    }

    public function test_notifications_target_current_painter_not_client_and_use_translated_subject(): void
    {
        $painter = $this->user([]);
        DB::table('users')->where('id', $painter->id)->update(['settings' => json_encode(['locale' => 'uk'])]);
        $this->order->forceFill(['user_id' => $this->editor->id])->save();
        $this->order->load('painterAssignment'); // Stale cached null must not affect sending.
        DB::table('painter_orders')->insert(['order_id' => $this->order->id, 'user_id' => $painter->id]);
        DB::table('user_messages')->insert(['id' => 1, 'admin_user_chat_title' => 'Заказ {order_id}']);
        DB::table('translations')->insert(['table_name' => 'user_messages', 'column_name' => 'admin_user_chat_title',
            'foreign_key' => 1, 'locale' => 'uk', 'value' => 'Замовлення {order_id}']);
        config(['admin_migration.painter_chat_notifications_enabled' => true]);
        $this->mock(SynvolveWebhookService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('notifyManagerMessageForOrder')->once()
                ->withArgs(fn ($id, $text, $context): bool => $id === $this->order->id && $context['source'] === 'orders_chats'
                    && $context['image_type'] === null && OrdersChats::query()->whereKey($context['message_id'])->exists())
                ->andReturn(true);
        });
        $result = $this->send(['comment' => '<script>unsafe</script>', 'user_id' => $this->editor->id]);
        $this->assertTrue($result['mailSent']);
        $this->assertTrue($result['webhookSent']);
        Mail::assertSent(AdminToPainterComment::class, fn ($mail): bool => $mail->hasTo($painter->email)
            && ! $mail->hasTo($this->editor->email) && $mail->subjectText === 'Замовлення '.$this->order->id && $mail->locale === 'uk');
        $html = (new AdminToPainterComment('<script>unsafe</script>', 'Test'))->render();
        $this->assertStringContainsString('&lt;script&gt;unsafe&lt;/script&gt;', $html);
        $this->assertStringNotContainsString('<script>unsafe</script>', $html);
    }

    public function test_failed_webhook_keeps_saved_message_without_assignment(): void
    {
        config(['admin_migration.painter_chat_notifications_enabled' => true]);
        $this->mock(SynvolveWebhookService::class, fn (MockInterface $mock) => $mock
            ->shouldReceive('notifyManagerMessageForOrder')->once()->andThrow(new \RuntimeException('Fake failure')));
        $result = $this->send();
        $this->assertFalse($result['webhookSent']);
        $this->assertFalse($result['mailSent']);
        $this->assertDatabaseCount('orders_chats', 1);
        Mail::assertNothingSent();
    }

    public function test_mail_transport_failure_does_not_rollback_message_or_skip_webhook(): void
    {
        $painter = $this->user([]);
        DB::table('painter_orders')->insert(['order_id' => $this->order->id, 'user_id' => $painter->id]);
        config(['admin_migration.painter_chat_notifications_enabled' => true]);
        $this->mock(BestEffortMailService::class, fn (MockInterface $mock) => $mock
            ->shouldReceive('send')->once()->andThrow(new \RuntimeException('Fake SMTP failure')));
        $this->mock(SynvolveWebhookService::class, fn (MockInterface $mock) => $mock
            ->shouldReceive('notifyManagerMessageForOrder')->once()->andReturn(true));
        $result = $this->send();
        $this->assertFalse($result['mailSent']);
        $this->assertTrue($result['webhookSent']);
        $this->assertDatabaseCount('orders_chats', 1);
    }

    public function test_livewire_history_type_labels_and_nested_reply_and_read(): void
    {
        $message = $this->incoming(['is_img_sketch' => 1, 'comment' => '<script>unsafe</script>']);
        $this->incoming(['is_img_painter' => 1]);
        $this->actingAs($this->editor, 'filament');
        $table = Livewire::test(PainterChatTestTable::class)->mountAction(TestAction::make('viewPainterChat')->table($this->order));
        $html = $table->instance()->getMountedAction()->getModalContent()->render();
        foreach (['Художник (набросок)', 'Художник (картина)', '&lt;script&gt;unsafe&lt;/script&gt;', 'Не прочитано администратором'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        $this->assertStringNotContainsString('<script>unsafe</script>', $html);
        $this->assertSame(0, $message->fresh()->admin_is_read);
        $table->call('mountTableAction', 'sendPainterChatMessage', (string) $this->order->id)
            ->fillForm(['comment' => 'Livewire reply'])->callMountedAction()->assertHasNoErrors();
        $this->assertDatabaseHas('orders_chats', ['comment' => 'Livewire reply', 'is_admin' => 1]);
        Livewire::test(PainterChatTestTable::class)->mountAction(TestAction::make('viewPainterChat')->table($this->order))
            ->call('mountTableAction', 'readPainterChatMessage', (string) $this->order->id, ['message_id' => $message->id])
            ->assertHasNoErrors();
        $this->assertSame(1, $message->fresh()->admin_is_read);
        Mail::assertNothingSent();
    }

    public function test_livewire_reader_cannot_invoke_mutating_actions_and_ordinary_user_cannot_view(): void
    {
        $message = $this->incoming();
        $this->actingAs($this->user(['browse_admin', 'read_orders']), 'filament');
        $table = Livewire::test(PainterChatTestTable::class)->mountAction(TestAction::make('viewPainterChat')->table($this->order));
        $html = $table->instance()->getMountedAction()->getModalContent()->render();
        $this->assertStringNotContainsString('Ответить художнику', $html);
        $this->assertStringNotContainsString('Отметить прочитанным', $html);
        Livewire::test(PainterChatTestTable::class)
            ->assertActionHidden(TestAction::make('sendPainterChatMessage')->table($this->order))
            ->assertActionHidden(TestAction::make('readPainterChatMessage')->table($this->order))
            ->call('mountTableAction', 'sendPainterChatMessage', (string) $this->order->id)
            ->call('mountTableAction', 'readPainterChatMessage', (string) $this->order->id, ['message_id' => $message->id]);
        $this->assertDatabaseCount('orders_chats', 1);
        $this->assertSame(0, $message->fresh()->admin_is_read);
        $this->actingAs($this->user([]), 'filament');
        Livewire::test(PainterChatTestTable::class)->assertActionHidden(TestAction::make('viewPainterChat')->table($this->order));
    }

    public function test_inline_painter_composer_preserves_general_flags_without_notifications(): void
    {
        $this->actingAs($this->editor, 'filament');
        Livewire::test(OrderChatComposer::class, ['orderId' => $this->order->id, 'stream' => 'painter'])
            ->set('text', 'Inline painter')->call('send')->assertHasNoErrors()
            ->assertSet('text', '')->assertDispatched('order-chat-updated', orderId: $this->order->id);
        $this->assertDatabaseHas('orders_chats', ['orders_id' => $this->order->id, 'comment' => 'Inline painter', 'is_admin' => 1, 'is_img_painter' => 0, 'is_img_sketch' => 0]);
        Mail::assertNothingSent();
    }

    private function send(array $input = []): array
    {
        return app(OrderPainterChatService::class)->send($this->order, $this->editor, array_merge(['comment' => 'Ответ'], $input));
    }

    private function incoming(array $input = []): OrdersChats
    {
        return OrdersChats::query()->forceCreate(array_merge(['orders_id' => $this->order->id,
            'comment' => 'Тестовое входящее', 'updated_at' => '2026-01-01 12:00:00'], $input))->fresh();
    }

    private function user(array $permissions): User
    {
        $role = Role::query()->create(['name' => 'painter-chat-'.Role::query()->count()]);
        foreach ($permissions as $key) {
            $role->permissions()->attach(Permission::query()->firstOrCreate(['key' => $key]));
        }
        $id = DB::table('users')->insertGetId(['role_id' => $role->id, 'email' => 'painter-chat-'.$role->id.'@example.invalid']);

        return User::query()->findOrFail($id);
    }
}

class PainterChatTestTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table->query(Orders::query())->columns([TextColumn::make('id')])
            ->recordActions([OrderPainterChatActions::history(), OrderPainterChatActions::reply(), OrderPainterChatActions::read()]);
    }

    public function render()
    {
        return view('admin-tests::client-chat-table');
    }
}
