<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Orders\Tables\OrderClientChatActions;
use App\Mail\AdminToUserComment;
use App\Models\Orders;
use App\Models\OrderUserComments;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Admin\OrderClientChatService;
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

class OrderClientChatTest extends TestCase
{
    private Orders $order;

    private User $editor;

    protected function setUp(): void
    {
        parent::setUp();
        config(['admin_migration.client_chat_notifications_enabled' => false]);
        Mail::fake();
        $this->mock(SynvolveWebhookService::class, fn (MockInterface $mock) => $mock->shouldNotReceive('notifyManagerMessageForOrder'));
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('key');
            $table->timestamps();
        });
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
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status')->default('watching');
            $table->timestamps();
        });
        Schema::create('order_user_comments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('user_id');
            $table->text('comment');
            foreach (['is_admin', 'is_read', 'admin_is_read', 'is_img_sketch', 'is_img_painter', 'order_painter_image_id', 'order_user_image_id'] as $field) {
                $table->integer($field)->default(0);
            }
            $table->timestamps();
        });
        Schema::create('order_painter_images', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('image')->default('chat-test.jpg');
            $table->string('small_image')->nullable();
            $table->integer('status')->default(2);
            $table->boolean('is_show')->default(true);
            $table->boolean('is_img_sketch')->default(false);
            $table->boolean('is_img_painter')->default(false);
            $table->timestamps();
        });
        Schema::create('a_painter_images_status', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
        });
        Schema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->string('table_name');
            $table->string('column_name');
            $table->unsignedBigInteger('foreign_key');
            $table->string('locale');
            $table->text('value');
        });
        DB::table('a_painter_images_status')->insert(['id' => 2, 'title' => 'Checking']);
        DB::table('translations')->insert([
            'table_name' => 'a_painter_images_status', 'column_name' => 'title',
            'foreign_key' => 2, 'locale' => 'ru', 'value' => 'Проверка изображения',
        ]);
        $this->editor = $this->userWithPermissions(['browse_admin', 'browse_orders', 'read_orders', 'edit_orders']);
        $this->order = Orders::query()->forceCreate(['status' => 'watching']);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        view()->addNamespace('admin-tests', base_path('tests/Fixtures/views'));
    }

    public function test_general_reply_preserves_frontend_flags_without_external_notifications(): void
    {
        $result = $this->send(['comment' => '  Ответ клиенту  ']);
        $this->assertTrue($result['notificationsSuppressed']);
        $this->assertDatabaseHas('order_user_comments', [
            'order_id' => $this->order->id, 'user_id' => $this->editor->id,
            'comment' => 'Ответ клиенту', 'is_admin' => 1, 'is_read' => 0,
            'admin_is_read' => 1, 'is_img_sketch' => 0, 'is_img_painter' => 0,
            'order_painter_image_id' => 0, 'order_user_image_id' => 0,
        ]);
        Mail::assertNothingSent();
    }

    #[DataProvider('imageTypes')]
    public function test_reply_is_bound_to_the_correct_image_thread(string $type): void
    {
        $imageId = $this->image($type);
        $result = $this->send(['thread_type' => $type, 'image_id' => $imageId]);
        $this->assertSame($imageId, $result['message']->order_painter_image_id);
        $this->assertSame(1, $result['message']->{'is_img_'.$type});
    }

    public static function imageTypes(): array
    {
        return [['sketch'], ['painter']];
    }

    #[DataProvider('invalidInputs')]
    public function test_invalid_input_is_rejected_without_partial_message(array $input): void
    {
        try {
            $this->send($input);
            $this->fail('Invalid message was accepted.');
        } catch (ValidationException $exception) {
            $this->assertNotEmpty($exception->errors());
            $this->assertDatabaseCount('order_user_comments', 0);
        }
    }

    public static function invalidInputs(): array
    {
        return [
            'whitespace' => [['comment' => '   ']],
            'oversized' => [['comment' => str_repeat('a', 10001)]],
            'unknown thread' => [['thread_type' => 'foreign']],
            'forged general image' => [['image_id' => 1]],
            'missing image' => [['thread_type' => 'sketch']],
            'unknown image' => [['thread_type' => 'painter', 'image_id' => 999]],
        ];
    }

    public function test_image_from_another_order_and_wrong_type_are_rejected(): void
    {
        $foreign = Orders::query()->create();
        foreach ([$this->image('sketch', $foreign->id), $this->image('painter')] as $imageId) {
            try {
                $this->send(['thread_type' => 'sketch', 'image_id' => $imageId]);
                $this->fail('Invalid image binding accepted.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('image_id', $exception->errors());
            }
        }
        $this->assertDatabaseCount('order_user_comments', 0);
    }

    #[DataProvider('closedStatuses')]
    public function test_closed_order_blocks_image_reply_but_keeps_general_chat(string $status): void
    {
        $this->order->forceFill(['status' => $status])->save();
        try {
            $this->send(['thread_type' => 'painter', 'image_id' => $this->image('painter')]);
            $this->fail('Closed image thread accepted a message.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('thread_type', $exception->errors());
        }
        $this->send();
        $this->assertDatabaseCount('order_user_comments', 1);
    }

    public static function closedStatuses(): array
    {
        return [['completed'], ['sended'], ['send_lubanas']];
    }

    public function test_read_only_role_cannot_send_or_mark_read(): void
    {
        $reader = $this->userWithPermissions(['browse_admin', 'browse_orders', 'read_orders']);
        $message = $this->incoming();
        foreach (['send', 'read'] as $operation) {
            try {
                $service = app(OrderClientChatService::class);
                $operation === 'send'
                    ? $service->send($this->order, $reader, ['comment' => 'Denied', 'thread_type' => 'general'])
                    : $service->markMessageAsRead($this->order, $reader, $message->id);
                $this->fail('Read-only user changed the chat.');
            } catch (AuthorizationException $exception) {
                $this->assertSame(403, $exception->status() ?? 403);
            }
        }
        $this->assertDatabaseCount('order_user_comments', 1);
        $this->assertSame(0, $message->fresh()->admin_is_read);
    }

    public function test_read_is_explicit_targeted_idempotent_and_preserves_client_state_and_dates(): void
    {
        $target = $this->incoming(['is_read' => 1]);
        $other = $this->incoming();
        $service = app(OrderClientChatService::class);
        $this->assertSame(1, $service->markMessageAsRead($this->order, $this->editor, $target->id));
        $this->assertSame(0, $service->markMessageAsRead($this->order, $this->editor, $target->id));
        $this->assertSame(1, $target->fresh()->is_read);
        $this->assertEquals($target->updated_at, $target->fresh()->updated_at);
        $this->assertSame(0, $other->fresh()->admin_is_read);
        $outgoing = $this->send()['message'];
        $this->assertSame(0, $service->markMessageAsRead($this->order, $this->editor, $outgoing->id));
    }

    public function test_read_rejects_cross_order_message(): void
    {
        $message = $this->incoming(['order_id' => Orders::query()->create()->id]);
        try {
            app(OrderClientChatService::class)->markMessageAsRead($this->order, $this->editor, $message->id);
            $this->fail('Cross-order read accepted.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        $this->assertSame(0, $message->fresh()->admin_is_read);
    }

    public function test_webhook_failure_does_not_lose_the_saved_message(): void
    {
        config(['admin_migration.client_chat_notifications_enabled' => true]);
        $this->mock(SynvolveWebhookService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('notifyManagerMessageForOrder')->once()->andThrow(new \RuntimeException('Fake failure'));
        });
        $result = $this->send();
        $this->assertFalse($result['webhookSent']);
        $this->assertFalse($result['notificationsSuppressed']);
        $this->assertDatabaseCount('order_user_comments', 1);
        Mail::assertNothingSent();
    }

    public function test_enabled_notifications_use_existing_mail_and_webhook_with_persisted_message_id(): void
    {
        Schema::create('user_messages', function (Blueprint $table): void {
            $table->id();
            $table->string('user_painter_mail_subject');
        });
        DB::table('user_messages')->insert(['user_painter_mail_subject' => 'Сообщение по заказу']);
        $this->order->forceFill(['user_id' => $this->editor->id])->save();
        config(['admin_migration.client_chat_notifications_enabled' => true]);
        $this->mock(SynvolveWebhookService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('notifyManagerMessageForOrder')->once()
                ->withArgs(fn ($orderId, $text, $context): bool => $orderId === $this->order->id
                    && $text === '<script>unsafe</script>'
                    && OrderUserComments::query()->whereKey($context['message_id'])->exists())
                ->andReturn(true);
        });
        $result = $this->send(['comment' => '<script>unsafe</script>']);
        $this->assertTrue($result['mailSent']);
        $this->assertTrue($result['webhookSent']);
        Mail::assertSent(AdminToUserComment::class, fn ($mail): bool => $mail->hasTo($this->editor->email)
            && $mail->data['text'] === '&lt;script&gt;unsafe&lt;/script&gt;'
            && $mail->data['order_id'] === $this->order->id);
    }

    public function test_history_preserves_orphan_messages_and_escapes_html(): void
    {
        $this->incoming(['order_painter_image_id' => 999, 'comment' => '<script>unsafe</script>']);
        $this->actingAs($this->editor, 'filament');
        $table = Livewire::test(ClientChatTestTable::class)
            ->mountAction(TestAction::make('viewClientChat')->table($this->order));
        $html = $table->instance()->getMountedAction()->getModalContent()->render();
        $this->assertStringContainsString('Переписка по недоступным изображениям', $html);
        $this->assertStringContainsString('&lt;script&gt;unsafe&lt;/script&gt;', $html);
        $this->assertStringNotContainsString('<script>unsafe</script>', $html);
    }

    public function test_livewire_history_is_read_only_and_reply_uses_the_production_action(): void
    {
        $message = $this->incoming();
        $this->actingAs($this->editor, 'filament');
        $table = Livewire::test(ClientChatTestTable::class)
            ->mountAction(TestAction::make('viewClientChat')->table($this->order));
        $html = $table->instance()->getMountedAction()->getModalContent()->render();
        $this->assertStringContainsString('Общая переписка', $html);
        $this->assertStringContainsString('Тестовое входящее', $html);
        $this->assertSame(0, $message->fresh()->admin_is_read);
        $table->call('mountTableAction', 'sendClientChatMessage', (string) $this->order->id)
            ->assertActionDataSet(['thread_type' => 'general'])
            ->fillForm(['comment' => 'Ответ через Livewire'])
            ->callMountedAction()->assertHasNoErrors();
        $this->assertDatabaseHas('order_user_comments', ['comment' => 'Ответ через Livewire', 'is_admin' => 1]);
        Mail::assertNothingSent();
    }

    public function test_livewire_image_reply_prefill_and_explicit_read(): void
    {
        $imageId = $this->image('sketch');
        $message = $this->incoming(['order_painter_image_id' => $imageId, 'is_img_sketch' => 1]);
        $this->actingAs($this->editor, 'filament');
        $table = Livewire::test(ClientChatTestTable::class)
            ->mountAction(TestAction::make('viewClientChat')->table($this->order));
        $html = $table->instance()->getMountedAction()->getModalContent()->render();
        $this->assertStringContainsString('Наброски', $html);
        $this->assertStringContainsString('Проверка изображения', $html);
        $this->assertStringContainsString('Файл отсутствует в локальном хранилище', $html);
        $this->assertStringContainsString('Тестовое входящее', $html);
        $table->call('mountTableAction', 'sendClientChatMessage', (string) $this->order->id, ['thread_type' => 'sketch', 'image_id' => $imageId])
            ->assertActionDataSet(['thread_type' => 'sketch', 'image_id' => $imageId])
            ->fillForm(['comment' => 'Ответ по наброску'])
            ->callMountedAction()->assertHasNoErrors();
        $this->assertDatabaseHas('order_user_comments', ['comment' => 'Ответ по наброску', 'order_painter_image_id' => $imageId, 'is_img_sketch' => 1]);
        Livewire::test(ClientChatTestTable::class)
            ->mountAction(TestAction::make('viewClientChat')->table($this->order))
            ->call('mountTableAction', 'readClientChatMessage', (string) $this->order->id, ['message_id' => $message->id])
            ->assertHasNoErrors();
        $this->assertSame(1, $message->fresh()->admin_is_read);
    }

    public function test_livewire_read_only_role_cannot_invoke_mutating_actions(): void
    {
        $this->actingAs($this->userWithPermissions(['browse_admin', 'read_orders']), 'filament');
        $message = $this->incoming();
        Livewire::test(ClientChatTestTable::class)
            ->assertActionHidden(TestAction::make('sendClientChatMessage')->table($this->order))
            ->assertActionHidden(TestAction::make('readClientChatMessage')->table($this->order))
            ->call('mountTableAction', 'readClientChatMessage', (string) $this->order->id, ['message_id' => $message->id]);
        $this->assertSame(0, $message->fresh()->admin_is_read);
        $this->assertDatabaseCount('order_user_comments', 1);
    }

    private function send(array $input = []): array
    {
        return app(OrderClientChatService::class)->send($this->order, $this->editor, array_merge(['comment' => 'Ответ', 'thread_type' => 'general'], $input));
    }

    private function incoming(array $attributes = []): OrderUserComments
    {
        return OrderUserComments::query()->forceCreate(array_merge([
            'order_id' => $this->order->id, 'user_id' => 999,
            'comment' => 'Тестовое входящее', 'updated_at' => '2026-01-01 12:00:00',
        ], $attributes))->fresh();
    }

    private function image(string $type, ?int $orderId = null): int
    {
        return DB::table('order_painter_images')->insertGetId(['order_id' => $orderId ?? $this->order->id, 'is_img_'.$type => 1]);
    }

    private function userWithPermissions(array $permissions): User
    {
        $role = Role::query()->create(['name' => 'chat-'.Role::query()->count()]);
        foreach ($permissions as $key) {
            $role->permissions()->attach(Permission::query()->firstOrCreate(['key' => $key]));
        }
        $id = DB::table('users')->insertGetId(['role_id' => $role->id, 'email' => 'chat-'.$role->id.'@example.invalid']);

        return User::query()->findOrFail($id);
    }
}

class ClientChatTestTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table->query(Orders::query())->columns([TextColumn::make('id')])
            ->recordActions([OrderClientChatActions::history(), OrderClientChatActions::reply(), OrderClientChatActions::read()]);
    }

    public function render()
    {
        return view('admin-tests::client-chat-table');
    }
}
