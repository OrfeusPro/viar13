<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Orders\Tables\OrderArtistActions;
use App\Mail\SendPrainterToUserPicture;
use App\Models\Orders;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Admin\OrderArtistService;
use App\Services\BestEffortMailService;
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
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Livewire;
use Mockery\MockInterface;
use Tests\TestCase;

class OrderArtistServiceTest extends TestCase
{
    private Orders $order;

    protected function setUp(): void
    {
        parent::setUp();
        config(['admin_migration.artist_notifications_enabled' => false]);
        Mail::fake();

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
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
        });
        Schema::create('user_roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
        });
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->string('email');
            $table->string('nick')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->json('settings')->nullable();
            $table->string('last_ip')->nullable();
            $table->text('registration_page')->nullable();
            $table->text('referrer_url')->nullable();
            $table->json('utm_parameters')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status')->default('watching');
            $table->date('painter_endtime')->nullable();
            $table->boolean('painter_payed')->nullable();
            $table->boolean('is_show_painter_images')->nullable();
            $table->text('painter_sketch_images')->nullable();
            $table->text('painter_images')->nullable();
            $table->text('client_images')->nullable();
            $table->timestamps();
        });
        foreach (['painter_orders', 'printing_orders'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('order_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();
            });
        }
        Schema::create('a_painter_images_status', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
        });
        Schema::create('order_painter_images', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('image');
            $table->string('small_image')->nullable();
            $table->unsignedBigInteger('status')->nullable();
            $table->boolean('is_img_sketch')->default(false);
            $table->boolean('is_img_painter')->default(false);
            $table->timestamps();
        });
        Schema::create('user_messages', function (Blueprint $table): void {
            $table->id();
            $table->string('new_painter_order')->nullable();
            $table->text('new_painter_order_text')->nullable();
            $table->string('new_photo_subject')->nullable();
            $table->text('new_photo_text')->nullable();
            $table->timestamps();
        });
        Schema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->string('table_name');
            $table->string('column_name');
            $table->unsignedBigInteger('foreign_key');
            $table->string('locale');
            $table->text('value');
        });

        DB::table('a_painter_images_status')->insert([['id' => 1, 'title' => 'Новый'], ['id' => 2, 'title' => 'Принят']]);
        $this->order = Orders::query()->forceCreate(['status' => 'watching']);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        view()->addNamespace('admin-tests', base_path('tests/Fixtures/views'));
    }

    public function test_updates_complete_legacy_artist_state_atomically_without_external_mail(): void
    {
        $painter = $this->user(3, 'artist@example.test');
        $printing = $this->user(6, 'printing@example.test');
        $imageId = DB::table('order_painter_images')->insertGetId([
            'order_id' => $this->order->id, 'image' => 'painter.jpg', 'status' => 1,
            'is_img_painter' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $result = app(OrderArtistService::class)->update($this->order, [
            'painter_id' => $painter->id, 'printing_id' => $printing->id,
            'painter_endtime' => '2026-09-12', 'painter_payed' => true,
            'is_show_painter_images' => true, 'images' => [['id' => $imageId, 'status' => 2]],
        ]);

        $this->assertTrue($result['notifications_suppressed']);
        $this->assertDatabaseHas('painter_orders', ['order_id' => $this->order->id, 'user_id' => $painter->id]);
        $this->assertDatabaseHas('printing_orders', ['order_id' => $this->order->id, 'user_id' => $printing->id]);
        $this->assertDatabaseHas('orders', ['id' => $this->order->id, 'painter_endtime' => '2026-09-12', 'painter_payed' => 1, 'is_show_painter_images' => 1]);
        $this->assertDatabaseHas('order_painter_images', ['id' => $imageId, 'status' => 2]);
        Mail::assertNothingSent();
    }

    public function test_replacement_and_removal_leave_one_assignment_per_order(): void
    {
        $old = $this->user(3, 'old@example.test');
        $new = $this->user(3, 'new@example.test');
        DB::table('painter_orders')->insert([
            ['order_id' => $this->order->id, 'user_id' => $old->id, 'created_at' => now(), 'updated_at' => now()],
            ['order_id' => $this->order->id, 'user_id' => $new->id, 'created_at' => now(), 'updated_at' => now()],
            ['order_id' => $this->order->id, 'user_id' => $new->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $service = app(OrderArtistService::class);
        $service->update($this->order, $this->payload(['painter_id' => $new->id]));
        $this->assertSame([$new->id], DB::table('painter_orders')->where('order_id', $this->order->id)->pluck('user_id')->all());
        $service->update($this->order, $this->payload());
        $this->assertDatabaseMissing('painter_orders', ['order_id' => $this->order->id]);
    }

    public function test_opt_in_preserves_both_legacy_email_branches_through_best_effort_mail(): void
    {
        $customer = $this->user(2, 'customer@example.test', ['settings' => json_encode(['locale' => 'ru'])]);
        $painter = $this->user(3, 'artist@example.test', ['settings' => json_encode(['locale' => 'ru'])]);
        $this->order->forceFill(['user_id' => $customer->id])->save();
        DB::table('user_messages')->insert([
            'new_painter_order' => 'Новый заказ', 'new_painter_order_text' => 'Заказ',
            'new_photo_subject' => 'Новые изображения', 'new_photo_text' => 'Изображения доступны',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        config(['admin_migration.artist_notifications_enabled' => true]);
        $this->mock(BestEffortMailService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('attempt')->once()->withArgs(fn ($callback, $purpose, $context): bool => is_callable($callback)
                && $purpose === 'admin_artist_assignment' && $context['order_id'] === $this->order->id)->andReturnTrue();
            $mock->shouldReceive('send')->once()->withArgs(fn ($recipient, $mailable, $purpose, $context): bool => $recipient === 'customer@example.test'
                && $mailable instanceof SendPrainterToUserPicture && $purpose === 'admin_artist_images_visible'
                && $context['order_id'] === $this->order->id)->andReturnTrue();
        });

        $result = app(OrderArtistService::class)->update($this->order, $this->payload([
            'painter_id' => $painter->id,
            'is_show_painter_images' => true,
        ]));

        $this->assertFalse($result['notifications_suppressed']);
    }

    public function test_invalid_role_or_foreign_image_rolls_back_every_change(): void
    {
        $wrongRole = $this->user(4, 'manager@example.test');
        $foreignOrder = Orders::query()->forceCreate(['status' => 'watching']);
        $foreignImage = DB::table('order_painter_images')->insertGetId([
            'order_id' => $foreignOrder->id, 'image' => 'foreign.jpg', 'status' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        foreach ([
            $this->payload(['painter_id' => $wrongRole->id]),
            $this->payload(['images' => [['id' => $foreignImage, 'status' => 2]], 'painter_payed' => true]),
        ] as $payload) {
            try {
                app(OrderArtistService::class)->update($this->order, $payload);
                $this->fail('ValidationException was not thrown.');
            } catch (ValidationException) {
                $this->assertDatabaseMissing('painter_orders', ['order_id' => $this->order->id]);
                $this->assertDatabaseHas('orders', ['id' => $this->order->id, 'painter_payed' => null]);
            }
        }
    }

    public function test_column_renders_legacy_data_previews_and_management_control(): void
    {
        $painter = $this->user(3, 'artist@example.test', ['nick' => 'Painter']);
        DB::table('painter_orders')->insert(['order_id' => $this->order->id, 'user_id' => $painter->id]);
        DB::table('order_painter_images')->insert([
            'order_id' => $this->order->id, 'image' => 'picture.jpg', 'status' => 2, 'is_img_painter' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $record = $this->order->fresh()->load(['painterAssignment.user', 'printingAssignment.user', 'order_painter_images.statusDefinition']);
        $html = view('filament.tables.columns.order-artist', ['getRecord' => fn () => $record])->render();
        foreach (['Painter', 'artist@example.test', 'Картины:', 'Принят', 'Менеджер печати', 'не назначен'] as $text) {
            $this->assertStringContainsString($text, $html);
        }

        $record->painterAssignment()->delete();
        $record->forceFill(['is_show_painter_images' => 1])->save();
        $record->unsetRelation('painterAssignment');
        $html = view('filament.tables.columns.order-artist', ['getRecord' => fn () => $record])->render();
        $this->assertStringContainsString('Показывать картины клиенту:', $html);
        $this->assertStringContainsString('<strong>да</strong>', $html);
    }

    public function test_livewire_action_requires_edit_orders_permission(): void
    {
        $editor = $this->admin(['edit_orders']);
        $this->actingAs($editor, 'filament');
        Livewire::test(ArtistTestTable::class)
            ->mountAction(TestAction::make('manageArtist')->table($this->order))
            ->fillForm($this->payload(['painter_payed' => true]))
            ->callMountedAction()
            ->assertHasNoErrors();
        $this->assertSame(1, (int) $this->order->fresh()->painter_payed);

        $this->actingAs($this->admin(['read_orders']), 'filament');
        Livewire::test(ArtistTestTable::class)
            ->assertActionHidden(TestAction::make('manageArtist')->table($this->order))
            ->call('mountTableAction', 'manageArtist', (string) $this->order->id);
        $this->assertSame(1, (int) $this->order->fresh()->painter_payed);
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'painter_id' => null, 'printing_id' => null, 'painter_endtime' => null,
            'painter_payed' => false, 'is_show_painter_images' => false, 'images' => [],
        ], $override);
    }

    private function user(int $roleId, string $email, array $extra = []): User
    {
        return User::query()->forceCreate(array_merge(['role_id' => $roleId, 'email' => $email], $extra));
    }

    private function admin(array $permissions): User
    {
        $role = Role::query()->create(['name' => 'artist-admin-'.Role::query()->count()]);
        foreach ($permissions as $key) {
            $role->permissions()->attach(Permission::query()->firstOrCreate(['key' => $key]));
        }

        return $this->user($role->id, 'admin-'.$role->id.'@example.test');
    }
}

class ArtistTestTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table->query(Orders::query())->columns([TextColumn::make('id')])
            ->recordActions([OrderArtistActions::manage()]);
    }

    public function render()
    {
        return view('admin-tests::client-chat-table');
    }
}
