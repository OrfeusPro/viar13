<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Orders\Pages\EditOrders;
use App\Filament\Resources\Orders\Schemas\OrderMediaForm;
use App\Filament\Resources\Orders\Tables\OrderArtistActions;
use App\Mail\SendPrainterToUserPicture;
use App\Models\Orders;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Admin\OrderArtistService;
use App\Services\Admin\OrderInvoiceService;
use App\Services\Admin\OrderLegacyMediaStatusService;
use App\Services\Admin\OrderMediaRemovalService;
use App\Services\Admin\UpdateOrderService;
use App\Services\BestEffortMailService;
use App\Services\Payment\OrderPaymentRequestService;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema as FormSchema;
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
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Livewire;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OrderArtistServiceTest extends TestCase
{
    private Orders $order;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mock(OrderInvoiceService::class)->shouldReceive('refreshDocument')->andReturn('https://example.test/invoice.pdf')->byDefault();
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
            foreach (['painter_sketch_images', 'painter_images'] as $prefix) {
                $table->integer($prefix.'_status')->nullable();
                $table->timestamp($prefix.'_status_date')->nullable();
            }
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

    #[DataProvider('fullEditRoles')]
    public function test_full_edit_role_roundtrip_and_revoked_save_preserve_other_data(string $role): void
    {
        [$editor, $items] = $this->prepareFullEditFixture();
        DB::table('roles')->where('id', $editor->role_id)->update(['name' => $role]);
        $this->actingAs(User::findOrFail($editor->id), 'filament');
        $before = $this->order->fresh();
        $images = DB::table('order_painter_images')->get()->toJson();
        $comments = DB::table('order_user_comments')->get()->toJson();
        $page = Livewire::test(EditOrders::class, ['record' => $this->order->getRouteKey()])
            ->assertSuccessful()->assertSee('Оплата')->assertSee('Товары в заказе')
            ->set('data.admin_comment', 'Saved by '.$role)
            ->call('save')->assertHasNoErrors();
        $fresh = $this->order->fresh();
        $this->assertSame('Saved by '.$role, $fresh->admin_comment);
        $this->assertSame($items, $fresh->items);
        foreach (['price', 'sale_price', 'payment_status', 'payment', 'client_images', 'painter_sketch_images', 'painter_sketch_images_status'] as $field) {
            $this->assertEquals($before->{$field}, $fresh->{$field}, $field);
        }
        $this->assertSame('Fixture street', json_decode($fresh->delivery, true)['address']);
        $this->assertSame($images, DB::table('order_painter_images')->get()->toJson());
        $this->assertSame($comments, DB::table('order_user_comments')->get()->toJson());
        $page->set('data.admin_comment', 'Denied after revocation');
        DB::table('permission_role')->where('role_id', $editor->role_id)
            ->where('permission_id', Permission::where('key', 'edit_orders')->value('id'))->delete();
        $this->actingAs(User::findOrFail($editor->id), 'filament');
        $savedAttributes = $fresh->getAttributes();
        $page->call('save')->assertForbidden();
        $this->assertSame($savedAttributes, $this->order->fresh()->getAttributes());
        Livewire::test(EditOrders::class, ['record' => $this->order->getRouteKey()])->assertForbidden();
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    public static function fullEditRoles(): array
    {
        return ['printing' => ['printing'], 'lite_manager' => ['lite_manager']];
    }

    #[DataProvider('fullEditRoles')]
    public function test_open_full_edit_keeps_permitted_draft_when_role_changes(string $initialRole): void
    {
        [$editor, $items] = $this->prepareFullEditFixture();
        DB::table('roles')->where('id', $editor->role_id)->update(['name' => $initialRole]);
        $this->actingAs(User::findOrFail($editor->id), 'filament');
        $page = Livewire::test(EditOrders::class, ['record' => $this->order->getRouteKey()])
            ->set('data.admin_comment', 'Draft across role change');
        $targetRole = $initialRole === 'printing' ? 'lite_manager' : 'printing';
        DB::table('roles')->where('id', $editor->role_id)->update(['name' => $targetRole]);
        $this->actingAs(User::findOrFail($editor->id), 'filament');
        $this->assertTrue(auth('filament')->user()->can('update', $this->order));
        $page->call('save')->assertHasNoErrors();
        $this->assertSame('Draft across role change', $this->order->fresh()->admin_comment);
        $this->assertSame($items, $this->order->fresh()->items);
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    public function test_open_full_edit_payment_footer_rechecks_printing_role_before_calling_service(): void
    {
        [$editor] = $this->prepareFullEditFixture();
        DB::table('roles')->where('id', $editor->role_id)->update(['name' => 'lite_manager']);
        $this->actingAs(User::findOrFail($editor->id), 'filament');
        $this->mock(OrderPaymentRequestService::class, fn (MockInterface $mock) => $mock->shouldNotReceive('createForOrder'));
        $action = TestAction::make('createPaymentRequest')->schemaComponent('paymentRequests', 'form');
        $page = Livewire::test(EditOrders::class, ['record' => $this->order->getRouteKey()])
            ->set('data.admin_comment', 'Unsaved manager draft')
            ->mountAction($action)->assertActionMounted($action)
            ->fillForm(['amount' => 15, 'purpose' => 'Must not be created']);
        $before = $this->order->fresh()->getAttributes();
        DB::table('roles')->where('id', $editor->role_id)->update(['name' => 'printing']);
        $this->actingAs(User::findOrFail($editor->id), 'filament');
        $this->assertTrue(auth('filament')->user()->can('update', $this->order));
        $this->assertFalse(auth('filament')->user()->can('updateFinances', $this->order));
        $page->callMountedAction()->assertSet('data.admin_comment', 'Unsaved manager draft');
        $printingPage = Livewire::test(EditOrders::class, ['record' => $this->order->getRouteKey()])
            ->assertDontSee('Создать платёж');
        $printingPage->mountAction($action)->assertActionNotMounted();
        $this->assertDatabaseCount('order_payment_requests', 0);
        $this->assertSame($before, $this->order->fresh()->getAttributes());
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    private function prepareFullEditFixture(): array
    {
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Http::preventStrayRequests();
        Schema::create('order_payment_requests', function (Blueprint $table): void {
            $table->id();
            foreach (['order_id', 'user_id', 'created_by'] as $field) {
                $table->integer($field)->nullable();
            }
            foreach (['public_number', 'token', 'currency', 'purpose', 'status', 'customer_email', 'customer_first_name', 'customer_last_name', 'customer_country', 'selected_payment_method'] as $field) {
                $table->string($field)->nullable();
            }
            $table->decimal('amount', 10, 2);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
        Schema::create('order_user_comments', function (Blueprint $table): void {
            $table->id();
            $table->integer('order_id');
            $table->text('comment');
            $table->boolean('admin_is_read')->default(false);
            $table->timestamps();
        });
        $editor = $this->admin(['browse_admin', 'browse_orders', 'read_orders', 'edit_orders']);
        $this->actingAs($editor, 'filament');
        Schema::table('orders', function (Blueprint $table): void {
            foreach (['manager_id', 'catid', 'a_order_from'] as $field) {
                $table->integer($field)->nullable();
            }
            foreach (['payment_status', 'payment', 'approved_date', 'ur_name', ...UpdateOrderService::COMPANY_FIELDS] as $field) {
                $table->string($field)->nullable();
            }
            foreach (['price', 'sale_price', 'sale_eur', 'sale_percent', 'prepayment_price'] as $field) {
                $table->decimal($field, 10, 2)->nullable();
            }
            foreach (['items', 'delivery', 'admin_comment', 'comment'] as $field) {
                $table->text($field)->nullable();
            }
        });
        Schema::create('country_tels', function (Blueprint $table): void {
            $table->id();
            $table->string('country_code');
            $table->integer('sort');
        });
        Schema::create('a_order_from', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
        });
        Schema::create('gallery_items', function (Blueprint $table): void {
            $table->id();
            $table->integer('id_type');
            $table->string('name');
            foreach (['custom_size_prices', 'custom_size_prices_form2', 'custom_users_prices'] as $field) {
                $table->text($field)->nullable();
            }
        });
        foreach (['canvas_header', 'a_collage_head'] as $name) {
            Schema::create($name, function (Blueprint $table) use ($name): void {
                $table->id();
                $table->string($name === 'canvas_header' ? 'c_right_top' : 'name');
                $table->text('sizes_30x40')->nullable();
                $table->text('sizes_38x38')->nullable();
            });
        }
        DB::table('canvas_header')->insert(['id' => 1, 'c_right_top' => 'Canvas']);
        DB::table('a_collage_head')->insert(['id' => 1, 'name' => 'Collage']);
        DB::table('country_tels')->insert(['country_code' => 'LV', 'sort' => 1]);
        $items = json_encode([['name' => 'Fixture item', 'price' => 50, 'orig_images' => []]], JSON_THROW_ON_ERROR);
        $this->order->forceFill([
            'price' => 50, 'sale_price' => 50, 'payment_status' => 'not_payed', 'payment' => 'transfer',
            'items' => $items, 'delivery' => '{"country":"LV","sposob":"to_the_door","address":"Fixture street"}',
            'painter_sketch_images' => 'https://example.test/sketch.jpg', 'painter_sketch_images_status' => 1,
            'client_images' => 'https://example.test/remove.jpg,https://example.test/keep.jpg',
        ])->save();
        DB::table('order_user_comments')->insert([
            'order_id' => $this->order->id, 'comment' => 'Client image <script>escaped</script>',
            'created_at' => '2026-09-01 12:00:00', 'updated_at' => '2026-09-01 12:00:00',
        ]);

        return [$editor, $items];
    }

    public function test_full_edit_page_saves_draft_after_live_media_status_without_overwriting_media_or_items(): void
    {
        [$editor, $items] = $this->prepareFullEditFixture();
        $page = Livewire::test(EditOrders::class, ['record' => $this->order->getRouteKey()])
            ->assertSuccessful()->assertSee('Изображения заказа')->assertSee('Товары в заказе')
            ->assertSee('Заявки на оплату')->assertSee('Client image &lt;script&gt;escaped&lt;/script&gt;', false)
            ->assertSet('data.painter_sketch_images_status', 1)
            ->set('data.admin_comment', 'Draft before status update')
            ->set('data.painter_sketch_images_status', 2)->assertHasNoErrors()
            ->assertSet('data.admin_comment', 'Draft before status update');
        $gallery = $page->instance()->form->getComponentByStatePath('order_media');
        $this->assertNotNull($gallery);
        $token = OrderMediaForm::groups($this->order->fresh())['Дополнительные изображения клиента'][0]['removal'];
        $remove = TestAction::make('removeOrderMedia')->schemaComponent($gallery->getKey(isAbsolute: false), 'form')->arguments(['token' => $token]);
        $page->mountAction($remove)->assertActionMounted($remove);
        $this->assertStringContainsString('Удалить изображение из заказа?', $page->getMountedActionModalHtml());
        $this->assertSame('https://example.test/remove.jpg,https://example.test/keep.jpg', $this->order->fresh()->client_images);
        $this->assertTrue($page->instance()->getMountedAction()->isConfirmationRequired());
        $page->callMountedAction()->assertHasNoErrors()
            ->assertSet('data.admin_comment', 'Draft before status update')
            ->assertDontSee('https://example.test/remove.jpg')
            ->call('save')->assertHasNoErrors();
        $fresh = $this->order->fresh();
        $this->assertSame('Draft before status update', $fresh->admin_comment);
        $this->assertEquals(2, $fresh->painter_sketch_images_status);
        $this->assertSame('https://example.test/sketch.jpg', $fresh->painter_sketch_images);
        $this->assertSame($items, $fresh->items);
        $this->assertSame('https://example.test/keep.jpg', $fresh->client_images);
        $this->assertSame('Fixture street', json_decode($fresh->delivery, true)['address']);
        $createRequest = TestAction::make('createPaymentRequest')->schemaComponent('paymentRequests', 'form');
        $page->mountAction($createRequest)->assertActionMounted($createRequest)
            ->fillForm(['amount' => 12.5, 'purpose' => 'Edit page payment'])
            ->callMountedAction()->assertHasNoErrors()->assertSee('OPR-000001')->assertSee('Edit page payment');
        $this->assertDatabaseHas('order_payment_requests', ['order_id' => $this->order->id, 'amount' => 12.5]);
        $this->assertDatabaseHas('order_user_comments', ['order_id' => $this->order->id, 'admin_is_read' => 0]);
        $page->set('data.admin_comment', 'Must not persist');
        DB::table('permission_role')->where('role_id', $editor->role_id)
            ->where('permission_id', Permission::where('key', 'edit_orders')->value('id'))->delete();
        $this->actingAs(User::findOrFail($editor->id), 'filament');
        $page->call('save')->assertForbidden();
        $this->assertSame('Draft before status update', $fresh->refresh()->admin_comment);
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    public function test_editor_media_footer_action_uses_bound_order_and_refreshes_gallery_without_losing_drafts(): void
    {
        $this->actingAs($this->admin(['browse_admin', 'read_orders', 'edit_orders']), 'filament');
        $imageId = DB::table('order_painter_images')->insertGetId([
            'order_id' => $this->order->id, 'image' => 'https://example.test/picture.jpg', 'status' => 1,
            'is_img_painter' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $component = Livewire::test(ArtistMediaSchemaHarness::class, ['record' => $this->order])
            ->set('data.unrelated_draft', 'Keep pending edits');
        $action = TestAction::make('manageArtist')->schemaComponent('mediaSection', 'form');
        $component->mountAction($action)->assertActionMounted($action)
            ->fillForm($this->payload(['images' => [['id' => $imageId, 'status' => 2]]]))
            ->callMountedAction()->assertHasNoErrors()
            ->assertSet('data.unrelated_draft', 'Keep pending edits')->assertSee('Принят');
        $this->assertDatabaseHas('order_painter_images', ['id' => $imageId, 'order_id' => $this->order->id, 'status' => 2]);
        Mail::assertNothingSent();
    }

    public function test_editor_media_footer_stops_after_permission_revocation(): void
    {
        $editor = $this->admin(['browse_admin', 'read_orders', 'edit_orders']);
        $this->actingAs($editor, 'filament');
        $this->mock(OrderArtistService::class, fn (MockInterface $mock) => $mock->shouldNotReceive('update'));
        $action = TestAction::make('manageArtist')->schemaComponent('mediaSection', 'form');
        $component = Livewire::test(ArtistMediaSchemaHarness::class, ['record' => $this->order])
            ->mountAction($action)->assertActionMounted($action)->fillForm($this->payload());
        DB::table('permission_role')->where('role_id', $editor->role_id)
            ->where('permission_id', Permission::where('key', 'edit_orders')->value('id'))->delete();
        $this->actingAs(User::findOrFail($editor->id), 'filament');
        $component->callMountedAction();
        Livewire::test(ArtistMediaSchemaHarness::class, ['record' => $this->order])->assertDontSee('Художник и печать');
        $this->assertFalse(auth('filament')->user()->can('update', $this->order));
        $this->assertDatabaseHas('orders', ['id' => $this->order->id, 'painter_payed' => null]);
        Mail::assertNothingSent();
    }

    public function test_gallery_delete_confirmation_updates_record_preserves_draft_and_rejects_stale_token(): void
    {
        $this->actingAs($this->admin(['browse_admin', 'read_orders', 'edit_orders']), 'filament');
        $this->order->forceFill(['client_images' => 'https://example.test/one.jpg,https://example.test/two.jpg'])->save();
        $groups = OrderMediaForm::groups($this->order);
        $token = $groups['Дополнительные изображения клиента'][0]['removal'];
        $action = TestAction::make('removeOrderMedia')->schemaComponent('mediaSection.mediaGallery', 'form')->arguments(['token' => $token]);
        $component = Livewire::test(ArtistMediaSchemaHarness::class, ['record' => $this->order])
            ->set('data.unrelated_draft', 'Keep pending edits');
        $component->mountAction($action)->assertActionMounted($action);
        $this->assertTrue($component->instance()->getMountedAction()->isConfirmationRequired());
        $this->assertTrue($component->instance()->mountedActionShouldOpenModal());
        $this->assertSame('Удалить изображение из заказа?', $component->instance()->getMountedAction()->getModalHeading());
        $component->assertDispatched('sync-action-modals');
        $this->assertStringContainsString('one.jpg', $this->order->fresh()->client_images);
        $component->callMountedAction()->assertHasNoErrors()->assertSet('data.unrelated_draft', 'Keep pending edits');
        $this->assertSame('https://example.test/two.jpg', $this->order->fresh()->client_images);
        $component->mountAction($action)->assertActionMounted($action)->callMountedAction()
            ->assertNotified('Изображение не удалено');
        $this->assertSame('https://example.test/two.jpg', $this->order->fresh()->client_images);
        Mail::assertNothingSent();
    }

    public function test_gallery_delete_rejects_forged_and_cross_order_tokens_before_service(): void
    {
        $this->actingAs($this->admin(['browse_admin', 'read_orders', 'edit_orders']), 'filament');
        $this->order->forceFill(['client_images' => 'https://example.test/keep.jpg'])->save();
        $foreign = Orders::query()->forceCreate(['client_images' => 'https://example.test/foreign.jpg']);
        $foreignToken = OrderMediaForm::groups($foreign)['Дополнительные изображения клиента'][0]['removal'];
        $this->mock(OrderMediaRemovalService::class, fn (MockInterface $mock) => $mock->shouldNotReceive('remove'));
        $action = fn ($token) => TestAction::make('removeOrderMedia')->schemaComponent('mediaSection.mediaGallery', 'form')->arguments(['token' => $token]);
        Livewire::test(ArtistMediaSchemaHarness::class, ['record' => $this->order])
            ->mountAction($action('forged'))->assertActionMounted($action('forged'))
            ->callMountedAction()->assertNotified('Обновите галерею перед удалением');
        Livewire::test(ArtistMediaSchemaHarness::class, ['record' => $this->order])
            ->mountAction($action($foreignToken))->assertActionMounted($action($foreignToken))
            ->callMountedAction()->assertForbidden();
        $this->assertSame('https://example.test/keep.jpg', $this->order->fresh()->client_images);
        $this->assertSame('https://example.test/foreign.jpg', $foreign->fresh()->client_images);
        Mail::assertNothingSent();
    }

    public function test_open_gallery_delete_does_not_run_after_edit_permission_revocation(): void
    {
        $editor = $this->admin(['browse_admin', 'read_orders', 'edit_orders']);
        $this->actingAs($editor, 'filament');
        $this->order->forceFill(['client_images' => 'https://example.test/keep.jpg'])->save();
        $token = OrderMediaForm::groups($this->order)['Дополнительные изображения клиента'][0]['removal'];
        $action = TestAction::make('removeOrderMedia')->schemaComponent('mediaSection.mediaGallery', 'form')->arguments(['token' => $token]);
        $component = Livewire::test(ArtistMediaSchemaHarness::class, ['record' => $this->order])
            ->mountAction($action)->assertActionMounted($action);
        $this->mock(OrderMediaRemovalService::class, fn (MockInterface $mock) => $mock->shouldNotReceive('remove'));
        DB::table('permission_role')->where('role_id', $editor->role_id)
            ->where('permission_id', Permission::where('key', 'edit_orders')->value('id'))->delete();
        $this->actingAs(User::findOrFail($editor->id), 'filament');
        $component->callMountedAction();
        $this->assertSame('https://example.test/keep.jpg', $this->order->fresh()->client_images);
    }

    public function test_media_removal_matches_original_row_and_legacy_contract_without_deleting_files(): void
    {
        $disk = Storage::fake('uploads');
        $disk->put('uploads/sketch.jpg', 'original sketch bytes');
        $disk->put('uploads/painting.jpg', 'original painting bytes');
        $author = $this->admin(['edit_orders']);
        $service = app(OrderMediaRemovalService::class);
        foreach (['sketch' => 'painter_sketch_images', 'painting' => 'painter_images'] as $type => $field) {
            $path = 'https://example.test/uploads/'.$type.'.jpg';
            $this->order->forceFill([$field => $path.',https://other.test/'.$type.'.jpg,uploads/keep.jpg'])->save();
            $id = DB::table('order_painter_images')->insertGetId([
                'order_id' => $this->order->id, 'image' => $path,
                'is_img_sketch' => $type === 'sketch', 'is_img_painter' => $type === 'painting',
            ]);
            $service->remove($this->order, $author, ['type' => $type, 'row_id' => $id, 'fingerprint' => $service::fingerprint($path)]);
            $this->assertDatabaseMissing('order_painter_images', ['id' => $id]);
            $this->assertSame('uploads/keep.jpg', $this->order->{$field});
        }
        $this->order->forceFill(['client_images' => ', uploads/first.jpg,uploads/second.jpg,'])->save();
        $service->remove($this->order, $author, ['type' => 'client', 'index' => 0, 'fingerprint' => $service::fingerprint($this->order->client_images)]);
        $this->assertSame('uploads/second.jpg', $this->order->client_images);
        $this->assertSame('original sketch bytes', $disk->get('uploads/sketch.jpg'));
        $this->assertSame('original painting bytes', $disk->get('uploads/painting.jpg'));
        Mail::assertNothingSent();
    }

    public function test_media_removal_rejects_stale_list_wrong_order_and_type_without_partial_changes(): void
    {
        $author = $this->admin(['edit_orders']);
        $service = app(OrderMediaRemovalService::class);
        $this->order->forceFill(['client_images' => 'uploads/one.jpg,uploads/two.jpg'])->save();
        $foreign = Orders::query()->forceCreate(['status' => 'watching']);
        $id = DB::table('order_painter_images')->insertGetId(['order_id' => $foreign->id, 'image' => 'uploads/foreign.jpg', 'is_img_painter' => 1]);
        foreach ([
            ['type' => 'client', 'index' => 0, 'fingerprint' => $service::fingerprint('stale')],
            ['type' => 'painting', 'row_id' => $id, 'fingerprint' => $service::fingerprint('uploads/foreign.jpg')],
            ['type' => 'sketch', 'row_id' => $id, 'fingerprint' => $service::fingerprint('uploads/foreign.jpg')],
        ] as $input) {
            try {
                $service->remove($this->order, $author, $input);
                $this->fail('Invalid deletion accepted.');
            } catch (ValidationException) {
                $this->assertDatabaseHas('order_painter_images', ['id' => $id]);
                $this->assertSame('uploads/one.jpg,uploads/two.jpg', $this->order->fresh()->client_images);
            }
        }
        $this->expectException(AuthorizationException::class);
        $service->remove($this->order, $this->admin(['read_orders']), ['type' => 'client', 'index' => 0, 'fingerprint' => $service::fingerprint($this->order->client_images)]);
    }

    public function test_legacy_status_selectors_save_immediately_without_changing_row_statuses_or_drafts(): void
    {
        $this->actingAs($this->admin(['browse_admin', 'read_orders', 'edit_orders']), 'filament');
        $this->order->forceFill([
            'painter_sketch_images' => 'https://example.test/sketch.jpg', 'painter_sketch_images_status' => 1,
            'painter_images' => 'https://example.test/painting.jpg', 'painter_images_status' => 1,
        ])->save();
        $before = $this->order->getRawOriginal('updated_at');
        $test = Livewire::test(ArtistMediaSchemaHarness::class, ['record' => $this->order])
            ->assertSet('data.painter_sketch_images_status', 1)
            ->set('data.unrelated_draft', 'Keep draft')
            ->set('data.painter_sketch_images_status', 2)->assertHasNoErrors()
            ->set('data.painter_images_status', 2)->assertHasNoErrors()
            ->assertSet('data.unrelated_draft', 'Keep draft');
        $fresh = $this->order->fresh();
        $this->assertEquals(2, $fresh->painter_sketch_images_status);
        $this->assertEquals(2, $fresh->painter_images_status);
        $this->assertNotNull($fresh->painter_sketch_images_status_date);
        $this->assertNotNull($fresh->painter_images_status_date);
        $this->assertSame($before, $fresh->getRawOriginal('updated_at'));
        $this->assertSame($this->order->painter_images, $fresh->painter_images);
        $this->assertDatabaseCount('order_painter_images', 0);
        Mail::assertNothingSent();
    }

    public function test_legacy_selector_validation_restores_value_and_general_submit_excludes_status_fields(): void
    {
        $this->actingAs($this->admin(['browse_admin', 'read_orders', 'edit_orders']), 'filament');
        $this->order->forceFill(['painter_sketch_images' => 'https://example.test/sketch.jpg', 'painter_sketch_images_status' => 1])->save();
        $test = Livewire::test(ArtistMediaSchemaHarness::class, ['record' => $this->order])
            ->set('data.unrelated_draft', 'Keep')
            ->set('data.painter_sketch_images_status', 999)
            ->assertHasErrors('data.painter_sketch_images_status')
            ->assertSet('data.painter_sketch_images_status', 1)
            ->assertSet('data.unrelated_draft', 'Keep');
        $this->assertEquals(1, $this->order->fresh()->painter_sketch_images_status);
        $test->set('data.painter_sketch_images_status', 2)->assertHasNoErrors()
            ->call('submitMedia')->assertHasNoErrors();
        $this->assertArrayNotHasKey('painter_sketch_images_status', $test->get('submitted'));
        $this->assertArrayNotHasKey('painter_images_status', $test->get('submitted'));
        Mail::assertNothingSent();
    }

    public function test_legacy_selector_rejects_database_row_created_after_form_open(): void
    {
        $this->actingAs($this->admin(['browse_admin', 'read_orders', 'edit_orders']), 'filament');
        $this->order->forceFill(['painter_sketch_images' => 'https://example.test/sketch.jpg', 'painter_sketch_images_status' => 1])->save();
        $test = Livewire::test(ArtistMediaSchemaHarness::class, ['record' => $this->order]);
        DB::table('order_painter_images')->insert(['order_id' => $this->order->id, 'image' => 'sketch.jpg', 'status' => 1, 'is_img_sketch' => 1]);
        $test->set('data.painter_sketch_images_status', 2)
            ->assertHasErrors('data.painter_sketch_images_status')->assertSet('data.painter_sketch_images_status', 1);
        $this->assertEquals(1, $this->order->fresh()->painter_sketch_images_status);
        $this->assertDatabaseHas('order_painter_images', ['order_id' => $this->order->id, 'status' => 1]);
    }

    public function test_legacy_selector_blocks_service_after_permission_revocation(): void
    {
        $editor = $this->admin(['browse_admin', 'read_orders', 'edit_orders']);
        $this->actingAs($editor, 'filament');
        $this->order->forceFill(['painter_sketch_images' => 'https://example.test/sketch.jpg', 'painter_sketch_images_status' => 1])->save();
        $test = Livewire::test(ArtistMediaSchemaHarness::class, ['record' => $this->order]);
        $this->mock(OrderLegacyMediaStatusService::class, fn (MockInterface $mock) => $mock->shouldNotReceive('update'));
        DB::table('permission_role')->where('role_id', $editor->role_id)
            ->where('permission_id', Permission::where('key', 'edit_orders')->value('id'))->delete();
        $this->actingAs(User::findOrFail($editor->id), 'filament');
        $this->assertFalse(auth('filament')->user()->can('update', $this->order));
        $test->set('data.painter_sketch_images_status', 2);
        $this->assertEquals(1, $this->order->fresh()->painter_sketch_images_status);
        Mail::assertNothingSent();
    }

    public function test_legacy_status_service_rejects_database_backed_group_and_invalid_status(): void
    {
        $author = $this->admin(['edit_orders']);
        $this->order->forceFill(['painter_images' => 'https://example.test/painting.jpg', 'painter_images_status' => 1])->save();
        DB::table('order_painter_images')->insert(['order_id' => $this->order->id, 'image' => 'picture.jpg', 'status' => 1, 'is_img_painter' => 1]);
        foreach ([2, 999] as $status) {
            try {
                app(OrderLegacyMediaStatusService::class)->update($this->order, $author, 'painting', $status);
                $this->fail('Invalid legacy status update accepted.');
            } catch (ValidationException) {
                $this->assertEquals(1, $this->order->fresh()->painter_images_status);
                $this->assertDatabaseHas('order_painter_images', ['order_id' => $this->order->id, 'status' => 1]);
            }
        }
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

    public function test_painting_status_action_changes_only_final_status_and_requires_edit_permission(): void
    {
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $id = DB::table('order_painter_images')->insertGetId([
            'order_id' => $this->order->id, 'image' => 'final.jpg', 'status' => 1, 'is_img_painter' => 1,
        ]);
        $this->actingAs($this->admin(['edit_orders']), 'filament');
        Livewire::test(ArtistTestTable::class)
            ->mountAction(TestAction::make('managePaintingStatuses')->table($this->order))
            ->fillForm(['images' => [['id' => $id, 'status' => 2]]])->callMountedAction()->assertHasNoErrors();
        $this->assertDatabaseHas('order_painter_images', ['id' => $id, 'status' => 2, 'image' => 'final.jpg']);
        $this->assertNull($this->order->fresh()->painter_payed);
        $this->assertDatabaseCount('painter_orders', 0);
        $this->actingAs($this->admin(['read_orders']), 'filament');
        Livewire::test(ArtistTestTable::class)->assertActionHidden(TestAction::make('managePaintingStatuses')->table($this->order));
        Mail::assertNothingSent();
    }

    public function test_painting_status_batch_rejects_sketch_or_other_order_atomically(): void
    {
        $final = DB::table('order_painter_images')->insertGetId([
            'order_id' => $this->order->id, 'image' => 'final.jpg', 'status' => 1, 'is_img_painter' => 1,
        ]);
        $sketch = DB::table('order_painter_images')->insertGetId([
            'order_id' => $this->order->id, 'image' => 'sketch.jpg', 'status' => 1, 'is_img_sketch' => 1,
        ]);
        $foreign = DB::table('order_painter_images')->insertGetId([
            'order_id' => 999, 'image' => 'foreign.jpg', 'status' => 1, 'is_img_painter' => 1,
        ]);
        foreach ([$sketch, $foreign] as $invalid) {
            try {
                app(OrderArtistService::class)->updatePaintingStatuses($this->order, ['images' => [
                    ['id' => $final, 'status' => 2], ['id' => $invalid, 'status' => 2],
                ]]);
                $this->fail('Invalid image accepted');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('images', $exception->errors());
            }
            $this->assertDatabaseHas('order_painter_images', ['id' => $final, 'status' => 1]);
            $this->assertDatabaseHas('order_painter_images', ['id' => $invalid, 'status' => 1]);
        }
        Mail::assertNothingSent();
    }

    public function test_open_artist_and_painting_forms_cannot_save_after_edit_permission_is_revoked(): void
    {
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $id = DB::table('order_painter_images')->insertGetId([
            'order_id' => $this->order->id, 'image' => 'final.jpg', 'status' => 1, 'is_img_painter' => 1,
        ]);
        $this->mock(OrderArtistService::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('update');
            $mock->shouldNotReceive('updatePaintingStatuses');
        });
        foreach (['manageArtist' => $this->payload(), 'managePaintingStatuses' => ['images' => [['id' => $id, 'status' => 2]]]] as $action => $payload) {
            $editor = $this->admin(['edit_orders']);
            $this->actingAs($editor, 'filament');
            $component = Livewire::test(ArtistTestTable::class)
                ->mountAction(TestAction::make($action)->table($this->order))
                ->assertActionMounted(TestAction::make($action)->table($this->order))->fillForm($payload);
            DB::table('permission_role')->where('role_id', $editor->role_id)->delete();
            $this->actingAs(User::findOrFail($editor->id), 'filament');
            $this->assertFalse(auth('filament')->user()->can('update', $this->order));
            $component->callMountedAction();
            $this->assertDatabaseHas('order_painter_images', ['id' => $id, 'status' => 1]);
            $this->assertNull($this->order->fresh()->painter_payed);
        }
        Mail::assertNothingSent();
    }

    public function test_printing_artist_column_keeps_final_and_client_images_without_sketch_or_management(): void
    {
        $editor = $this->admin(['edit_orders']);
        $editor->role->forceFill(['name' => 'printing'])->save();
        $this->actingAs($editor, 'filament');
        $painter = $this->user(3, 'artist@example.test', ['nick' => 'Painter']);
        DB::table('painter_orders')->insert(['order_id' => $this->order->id, 'user_id' => $painter->id]);
        foreach ([['image' => 'https://example.test/final.jpg', 'is_img_painter' => 1], ['image' => 'https://example.test/sketch.jpg', 'is_img_sketch' => 1]] as $image) {
            DB::table('order_painter_images')->insert($image + ['order_id' => $this->order->id, 'status' => 1]);
        }
        $this->order->forceFill(['client_images' => 'https://example.test/client.jpg', 'painter_endtime' => '2026-10-18'])->save();
        $record = $this->order->fresh()->load(['painterAssignment.user', 'printingAssignment.user', 'order_painter_images.statusDefinition']);
        $html = view('filament.tables.columns.order-artist', ['getRecord' => fn () => $record])->render();
        foreach (['Painter', 'final.jpg', 'client.jpg', 'updateOrderMediaStatus', "saveMedia('painting'"] as $visible) {
            $this->assertStringContainsString($visible, $html);
        }
        foreach (['sketch.jpg', "mountTableAction('manageArtist'", 'Время на заказ:', 'Заказ оплачен/выполнен:', 'Показывать картины клиенту:'] as $hidden) {
            $this->assertStringNotContainsString($hidden, $html);
        }
    }

    public function test_inline_artist_field_keeps_fresh_neighbouring_values_and_assignments(): void
    {
        $painter = $this->user(3, 'inline-painter@example.test');
        $printing = $this->user(6, 'inline-printing@example.test');
        $service = app(OrderArtistService::class);
        $service->update($this->order, $this->payload(['painter_id' => $painter->id, 'printing_id' => $printing->id]));
        $stale = $this->order->fresh();
        DB::table('orders')->where('id', $stale->id)->update(['is_show_painter_images' => 1, 'painter_endtime' => '2026-10-28']);
        $assignments = DB::table('printing_orders')->get()->toArray();
        $service->updateField($stale, 'painter_payed', true);
        $this->assertTrue((bool) $stale->painter_payed);
        $this->assertTrue((bool) $stale->is_show_painter_images);
        $this->assertSame('2026-10-28', substr((string) $stale->painter_endtime, 0, 10));
        $this->assertSame($painter->id, $stale->painterAssignment->user_id);
        $this->assertEquals($assignments, DB::table('printing_orders')->get()->toArray());
        $service->updateField($stale, 'printing_id', null);
        $this->assertDatabaseMissing('printing_orders', ['order_id' => $stale->id]);
        $service->updateField($stale, 'printing_id', $printing->id);
        $this->assertDatabaseHas('printing_orders', ['order_id' => $stale->id, 'user_id' => $printing->id]);
        $this->assertTrue((bool) $stale->painter_payed);
        Mail::assertNothingSent();
    }

    public function test_inline_artist_rejects_wrong_role_unknown_field_and_invalid_date_without_writes(): void
    {
        $wrong = $this->user(2, 'wrong@example.test');
        $before = $this->order->fresh()->getAttributes();
        foreach ([['painter_id', $wrong->id], ['printing_id', $wrong->id], ['painter_endtime', 'not-a-date'], ['images', []]] as [$field, $value]) {
            try {
                app(OrderArtistService::class)->updateField($this->order, $field, $value);
                $this->fail('Invalid inline update accepted.');
            } catch (ValidationException) {
                $this->assertSame($before, $this->order->fresh()->getAttributes());
                $this->assertDatabaseCount('painter_orders', 0);
                $this->assertDatabaseCount('printing_orders', 0);
            }
        }
    }

    public function test_inline_action_runs_without_modal_and_rejects_printing_even_with_edit_permission(): void
    {
        $editor = $this->admin(['edit_orders']);
        $this->actingAs($editor, 'filament');
        $action = TestAction::make('updateOrderArtistField')->table($this->order)->arguments(['field' => 'painter_payed', 'value' => true]);
        Livewire::test(ArtistTestTable::class)->callAction($action)->assertHasNoErrors();
        $this->assertTrue((bool) $this->order->fresh()->painter_payed);
        $editor->role->update(['name' => 'printing']);
        $this->actingAs($editor->fresh(), 'filament');
        Livewire::test(ArtistTestTable::class)->assertActionHidden($action);
        Mail::assertNothingSent();
    }

    public function test_inline_media_status_saves_only_selected_image_and_rejects_foreign_or_wrong_gallery(): void
    {
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Http::preventStrayRequests();
        $editor = $this->admin(['edit_orders']);
        $this->actingAs($editor, 'filament');
        $sketch = DB::table('order_painter_images')->insertGetId(['order_id' => $this->order->id, 'image' => 'sketch.jpg', 'status' => 1, 'is_img_sketch' => 1]);
        $painting = DB::table('order_painter_images')->insertGetId(['order_id' => $this->order->id, 'image' => 'painting.jpg', 'status' => 1, 'is_img_painter' => 1]);
        $foreign = DB::table('order_painter_images')->insertGetId(['order_id' => 999, 'image' => 'foreign.jpg', 'status' => 1, 'is_img_sketch' => 1]);
        $orderBefore = $this->order->fresh()->getRawOriginal();
        $action = fn (array $arguments) => TestAction::make('updateOrderMediaStatus')->table($this->order)->arguments($arguments);
        Livewire::test(ArtistTestTable::class)->callAction($action(['type' => 'sketch', 'image_id' => $sketch, 'status' => '2']))
            ->assertHasNoErrors()->assertNotified('Статус изображения обновлён');
        $this->assertDatabaseHas('order_painter_images', ['id' => $sketch, 'status' => 2, 'image' => 'sketch.jpg']);
        $this->assertNotNull(DB::table('order_painter_images')->where('id', $sketch)->value('updated_at'));
        foreach ([['sketch', $painting, 2], ['sketch', $foreign, 2], ['sketch', $sketch, 999]] as [$type, $imageId, $status]) {
            Livewire::test(ArtistTestTable::class)->callAction($action(['type' => $type, 'image_id' => $imageId, 'status' => $status]))
                ->assertNotified('Изменение не сохранено');
        }
        $this->assertDatabaseHas('order_painter_images', ['id' => $painting, 'status' => 1]);
        $this->assertDatabaseHas('order_painter_images', ['id' => $foreign, 'status' => 1]);
        $this->assertSame($orderBefore, $this->order->fresh()->getRawOriginal());
        $editor->role->update(['name' => 'printing']);
        $this->actingAs($editor->fresh(), 'filament');
        Livewire::test(ArtistTestTable::class)->callAction($action(['type' => 'painting', 'image_id' => $painting, 'status' => '2']))
            ->assertNotified('Статус изображения обновлён');
        Livewire::test(ArtistTestTable::class)
            ->call('mountTableAction', 'updateOrderMediaStatus', (string) $this->order->id, ['type' => 'sketch', 'image_id' => $sketch, 'status' => '1'])
            ->assertForbidden();
        $this->assertDatabaseHas('order_painter_images', ['id' => $sketch, 'status' => 2]);
        DB::table('permission_role')->delete();
        $this->actingAs($editor->fresh(), 'filament');
        Livewire::test(ArtistTestTable::class)->assertActionHidden($action(['type' => 'painting', 'image_id' => $painting, 'status' => '1']));
        Mail::assertNothingSent();
        Http::assertNothingSent();
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
            ->recordActions([OrderArtistActions::manage(), OrderArtistActions::paintingStatuses(), OrderArtistActions::updateField(), OrderArtistActions::updateMediaStatus()]);
    }

    public function render()
    {
        return view('admin-tests::client-chat-table');
    }
}

class ArtistMediaSchemaHarness extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public Orders $record;

    public array $data = [];

    public array $submitted = [];

    public function mount(Orders $record): void
    {
        $this->record = $record;
        $this->form->fill([]);
    }

    public function form(FormSchema $schema): FormSchema
    {
        return $schema->record($this->record)->statePath('data')
            ->components([OrderMediaForm::section()->key('mediaSection')]);
    }

    public function submitMedia(): void
    {
        $this->submitted = $this->form->getState();
    }

    public function render(): string
    {
        return '<div>{{ $this->form }}<x-filament-actions::modals /></div>';
    }
}
