<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Orders\Schemas\OrderInformationForm;
use App\Filament\Resources\Orders\Schemas\OrderItemsForm;
use App\Models\Orders;
use App\Models\User;
use App\Services\Admin\OrderInvoiceService;
use App\Services\Admin\OrderItemEditor;
use App\Services\Admin\OrderItemUploads;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Testing\TestAction;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema as DatabaseSchema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\Livewire;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class OrderItemEditorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mock(OrderInvoiceService::class)->shouldReceive('refreshDocument')->andReturn('https://example.test/invoice.pdf')->byDefault();
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Http::preventStrayRequests();
        Mail::fake();
        Storage::fake('uploads');
        Storage::fake('local');
        DatabaseSchema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->string('email');
            $table->string('nick')->nullable();
            $table->json('settings')->nullable();
        });
        foreach (['roles' => 'name', 'permissions' => 'key'] as $name => $column) {
            DatabaseSchema::create($name, function (Blueprint $table) use ($column): void {
                $table->id();
                $table->string($column);
                $table->timestamps();
            });
        }
        DatabaseSchema::create('permission_role', function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
        });
        DatabaseSchema::create('user_roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
        });
        DatabaseSchema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('items');
            $table->text('delivery')->nullable();
            $table->string('status')->default('watching');
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('sale_price', 10, 2)->default(0);
            $table->decimal('sale_eur', 10, 2)->default(5);
            $table->decimal('sale_percent', 10, 2)->default(10);
            $table->text('admin_comment')->nullable();
            $table->timestamps();
        });
        foreach (['gallery_holsts', 'gallery_boxes', 'gallery_decorations'] as $name) {
            DatabaseSchema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->string('name');
            });
        }
        DatabaseSchema::create('string_tranlations', function (Blueprint $table): void {
            $table->id();
            $table->string('standart_text');
            $table->string('express_text');
        });
        DatabaseSchema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->string('table_name');
            $table->string('column_name');
            $table->unsignedBigInteger('foreign_key');
            $table->string('locale');
            $table->text('value');
        });
        DB::table('gallery_holsts')->insert([['id' => 2, 'name' => 'Canvas'], ['id' => 5, 'name' => 'Gloss']]);
        DB::table('gallery_boxes')->insert([['id' => 1, 'name' => 'Exclusive'], ['id' => 3, 'name' => 'Standard box']]);
        DB::table('gallery_decorations')->insert([['id' => 1, 'name' => 'Art gel'], ['id' => 5, 'name' => 'None']]);
        DB::table('string_tranlations')->insert(['id' => 1, 'standart_text' => 'Standard', 'express_text' => 'Express']);
        DB::table('translations')->insert([
            ['table_name' => 'gallery_holsts', 'column_name' => 'name', 'foreign_key' => 5, 'locale' => 'lv', 'value' => 'LV Gloss'],
            ['table_name' => 'string_tranlations', 'column_name' => 'express_text', 'foreign_key' => 1, 'locale' => 'lv', 'value' => 'Ekspress'],
        ]);
        $this->order();
    }

    public function test_update_matches_original_item_fields_presentation_and_aggregates_without_changing_paths(): void
    {
        $this->mock(OrderInvoiceService::class)->shouldReceive('refreshDocument')->once()
            ->withArgs(fn (Orders $saved): bool => json_decode($saved->fresh()->items, true)[0]['name'] === 'Updated'
                && (float) $saved->fresh()->price === 1254.57)->andReturn('https://example.test/invoice.pdf');
        $order = Orders::findOrFail(1);
        $before = json_decode($order->items, true);
        $input = app(OrderItemEditor::class)->state($order)[0];
        $updated = app(OrderItemEditor::class)->update($order, array_replace($input, [
            'name' => 'Updated', 'price' => 1234.567, 'sizeId' => '50x70', 'size_name' => '50 × 70',
            'userComment' => '  New comment  ', 'manual_canvas_id' => 5, 'manual_gift_code' => 'G2',
            'manual_decoration_id' => 1, 'manual_orientation_code' => 'V2', 'manual_baget_code' => 'B2',
            'manual_express' => true, 'orig_images' => ['changed.jpg'],
        ]));
        $items = json_decode($updated->items, true);
        $this->assertSame('Updated', $items[0]['name']);
        $this->assertSame(1234.57, $items[0]['price']);
        foreach (['sumFormatedPrice', 'formatedPrice', 'formatedTotalPrice'] as $key) {
            $this->assertSame('1234.57 €', $items[0][$key]);
        }
        $this->assertSame(1234.57, $items[0]['sumPrice']);
        $this->assertSame('50x70', $items[0]['show']['size']);
        $this->assertSame('50 × 70', $items[0]['size_name']);
        $this->assertSame('New comment', $items[0]['userComment']);
        $this->assertSame('LV Gloss', $items[0]['show']['canvas']);
        $this->assertSame(['Exclusive'], $items[0]['show']['box']);
        $this->assertSame('Art gel', $items[0]['show']['decoration']);
        $this->assertSame('L2', $items[0]['manual_lac_code']);
        $this->assertSame('P0', $items[0]['manual_brushstrokes_code']);
        $this->assertSame('V2', $items[0]['manual_orientation_code']);
        $this->assertSame(0, $items[0]['is_manual_baget']);
        $this->assertSame(1, $items[0]['is_manual_express']);
        $this->assertSame('Ekspress 4 €', $items[0]['terms']);
        $this->assertSame(7.0, (float) $items['total_terms_price']);
        $this->assertSame($before[0]['orig_images'], $items[0]['orig_images']);
        $this->assertSame($before[0]['extension'], $items[0]['extension']);
        $this->assertSame($before[2], $items[2]);
        $this->assertSame($before['totalPrice'], $items['totalPrice']);
        $this->assertSame(1254.57, (float) $updated->price);
        $this->assertSame(1254.57, (float) $updated->sale_price);
        $this->assertSame(5.0, (float) $updated->sale_eur);
        $this->assertSame(10.0, (float) $updated->sale_percent);
        $delivery = json_decode($updated->delivery, true);
        $this->assertSame('keep-address', $delivery['address']);
        $this->assertSame(1, $delivery['is_manual_express']);
        Mail::assertNothingSent();
    }

    public function test_turning_express_off_recomputes_flags_terms_and_preserves_empty_size_fields(): void
    {
        $order = Orders::findOrFail(1);
        $items = json_decode($order->items, true);
        $items[2]['is_manual_express'] = 0;
        $items[0]['is_manual_express'] = 1;
        $items[0]['terms'] = 'Express';
        $order->forceFill(['items' => json_encode($items)])->save();
        $data = app(OrderItemEditor::class)->state($order)[0];
        $updated = app(OrderItemEditor::class)->update($order, array_replace($data, [
            'manual_express' => false, 'sizeId' => '', 'size_name' => '', 'userComment' => '',
        ]));
        $items = json_decode($updated->items, true);
        $this->assertSame('30x40', $items[0]['sizeId']);
        $this->assertSame('Original size', $items[0]['size_name']);
        $this->assertNull($items[0]['userComment']);
        $this->assertSame('Standard', $items[0]['terms']);
        $this->assertSame(0, $items[0]['terms_price']);
        $this->assertSame(3.0, (float) $items['total_terms_price']);
        $this->assertSame(0, json_decode($updated->delivery, true)['is_manual_express']);
    }

    public function test_invalid_or_stale_item_does_not_change_any_part_of_order(): void
    {
        $order = Orders::findOrFail(1);
        $data = app(OrderItemEditor::class)->state($order)[0];
        $before = $order->getAttributes();
        foreach ([['price' => -1], ['index' => 99], ['index' => 'totalPrice'], ['fingerprint' => str_repeat('0', 64)],
            ['manual_canvas_id' => 9], ['manual_gift_code' => 'G3'], ['manual_decoration_id' => 99],
            ['manual_orientation_code' => 'V5'], ['manual_baget_code' => 'B9'], ['manual_express' => 'invalid']] as $invalid) {
            try {
                app(OrderItemEditor::class)->update($order, array_replace($data, $invalid));
                $this->fail('Invalid item accepted.');
            } catch (ValidationException) {
                $this->assertSame($before, $order->refresh()->getAttributes());
            }
        }
        $items = json_decode($order->items, true);
        $items[0]['name'] = 'Concurrent';
        DB::table('orders')->where('id', 1)->update(['items' => json_encode($items)]);
        try {
            app(OrderItemEditor::class)->update($order, $data);
            $this->fail('Stale item accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('fingerprint', $exception->errors());
            $this->assertSame('Concurrent', json_decode($order->refresh()->items, true)[0]['name']);
        }
    }

    public function test_schema_action_saves_only_selected_position_and_refreshes_parent_totals(): void
    {
        $this->actingAs($this->actor(true), 'filament');
        $test = Livewire::test(OrderItemsFormHarness::class);
        $keys = array_keys($test->get('data.items_edit'));
        $test->set('data.items_edit.'.$keys[0].'.price', 50)
            ->set('data.items_edit.'.$keys[1].'.name', 'Unsaved other draft')
            ->callAction($this->action($keys[0]))->assertHasNoErrors()
            ->assertSet('data.price', 70)
            ->assertSet('data.sale_price', 70)
            ->assertSet('data.items_edit.'.$keys[1].'.name', 'Unsaved other draft')
            ->call('mainState')->assertHasNoErrors();
        $this->assertArrayNotHasKey('items_edit', $test->get('submitted'));
        $this->assertSame('Other', json_decode(Orders::findOrFail(1)->items, true)[2]['name']);
        $before = Orders::findOrFail(1)->items;
        $test->set('data.items_edit.'.$keys[0].'.price', -1)
            ->callAction($this->action($keys[0]))
            ->assertHasErrors(['data.items_edit.'.$keys[0].'.price']);
        $this->assertSame($before, Orders::findOrFail(1)->items);
        // A main submit excludes item drafts; the item's previous validation error may remain visible.
        $test->set('submitted', ['pending' => true])->call('mainState')->assertSet('submitted', []);
        $this->assertArrayNotHasKey('items_edit', $test->get('submitted'));
    }

    public function test_reader_cannot_execute_item_update_action(): void
    {
        $this->actingAs($this->actor(false), 'filament');
        $before = Orders::findOrFail(1)->items;
        $test = Livewire::test(OrderItemsFormHarness::class);
        $key = array_key_first($test->get('data.items_edit'));
        $test->assertActionHidden($this->action($key))
            ->assertDontSee('Добавить товар')
            ->assertActionHidden(TestAction::make('attachOrderItemImages')->schemaComponent('items_edit', 'form')->arguments(['item' => $key]))
            ->set('data.items_edit.'.$key.'.price', 999)
            ->call('mountAction', 'updateOrderItem', ['item' => $key], ['schemaComponent' => 'form.items_edit'])
            ->call('mountAction', 'addOrderItem', [], ['schemaComponent' => 'form.orderItemActions']);
        $this->assertSame($before, Orders::findOrFail(1)->items);
    }

    public function test_legacy_defaults_and_numeric_size_are_filled_without_mutating_stored_item(): void
    {
        $order = Orders::findOrFail(1);
        $items = json_decode($order->items, true);
        $items[0] = array_replace($items[0], ['sizeId' => 14, 'holst_id' => 5, 'boxIds' => '[2,1]',
            'manual_lac_code' => 'L2', 'forma_id' => 4, 'is_manual_baget' => 1, 'terms' => 'Kiirsaadetis']);
        $order->forceFill(['items' => json_encode($items)])->save();
        $before = $order->items;
        $state = app(OrderItemEditor::class)->state($order);
        $this->assertCount(2, $state);
        $this->assertSame(2, $state[1]['index']);
        $this->assertSame('14', $state[0]['sizeId']);
        $this->assertSame(5, $state[0]['manual_canvas_id']);
        $this->assertSame('G2', $state[0]['manual_gift_code']);
        $this->assertSame(1, $state[0]['manual_decoration_id']);
        $this->assertSame('V4', $state[0]['manual_orientation_code']);
        $this->assertSame('B1', $state[0]['manual_baget_code']);
        $this->assertTrue($state[0]['manual_express']);
        $this->assertSame($before, $order->refresh()->items);
        $updated = app(OrderItemEditor::class)->update($order, array_replace($state[0], ['userComment' => '0']));
        $this->assertSame('0', json_decode($updated->items, true)[0]['userComment']);
    }

    public function test_concurrent_other_position_is_preserved_and_used_in_total(): void
    {
        $order = Orders::findOrFail(1);
        $data = app(OrderItemEditor::class)->state($order)[0];
        $items = json_decode($order->items, true);
        $items[2]['price'] = 200;
        $items[2]['extension'] = 'Concurrent';
        $items[2]['is_manual_express'] = '1.0';
        DB::table('orders')->where('id', 1)->update(['items' => json_encode($items)]);
        $updated = app(OrderItemEditor::class)->update($order, array_replace($data, ['price' => 50]));
        $this->assertSame(250.0, (float) $updated->price);
        $this->assertSame($items[2], json_decode($updated->items, true)[2]);
        $this->assertSame(1, json_decode($updated->delivery, true)['is_manual_express']);
    }

    public function test_broken_items_or_delivery_are_rejected_without_overwriting_json(): void
    {
        $order = Orders::findOrFail(1);
        $data = app(OrderItemEditor::class)->state($order)[0];
        foreach (['items', 'delivery'] as $field) {
            $original = $order->{$field};
            DB::table('orders')->where('id', 1)->update([$field => '{broken']);
            $before = $order->refresh()->getAttributes();
            try {
                app(OrderItemEditor::class)->update($order, $data);
                $this->fail('Broken order JSON overwritten.');
            } catch (ValidationException) {
                $this->assertSame($before, $order->refresh()->getAttributes());
            }
            DB::table('orders')->where('id', 1)->update([$field => $original]);
            $order->refresh();
        }
    }

    public function test_revoked_edit_permission_blocks_action_from_an_already_open_form(): void
    {
        $editor = $this->actor(true);
        $this->actingAs($editor, 'filament');
        $test = Livewire::test(OrderItemsFormHarness::class);
        $key = array_key_first($test->get('data.items_edit'));
        $before = Orders::findOrFail(1)->getAttributes();
        DB::table('permission_role')->delete();
        $this->actingAs($editor->fresh(), 'filament');
        $test->set('data.items_edit.'.$key.'.price', 999)
            ->call('mountAction', 'updateOrderItem', ['item' => $key], ['schemaComponent' => 'form.items_edit']);
        $this->assertSame($before, Orders::findOrFail(1)->getAttributes());
    }

    public function test_information_blocks_match_original_values_and_escape_content_without_writes(): void
    {
        $order = Orders::findOrFail(1);
        $before = $order->getAttributes();
        $order->setAttribute('all_sales', json_encode(['facebook_sale' => 5, 'used_bonuses' => 0,
            'used_coupon' => 7, 'used_coupon_30_40' => 1, 'dates_sale' => 2, 'ignored' => 99]));
        $sales = OrderInformationForm::sales($order);
        $this->assertSame(['Скидка facebook' => '5 %', 'Скидка за бонусы' => 'нет',
            'Скидка за купон' => '7 €', 'Скидка на товар 30x40' => 'да', 'Скидка за 2 даты' => '2 %'], $sales);
        $html = view('filament.orders.existing-sales', compact('sales'))->render();
        $this->assertStringContainsString('Скидка на товар 30x40', $html);
        $order->setAttribute('items', json_encode([['content' => 'Line &lt;b&gt;<br><strong>Second</strong><br/>Third'],
            ['content' => '</textarea><img src=x onerror="alert(1)">Safe']]));
        $contents = OrderInformationForm::contents($order);
        $this->assertSame(["Line <b>\nSecond\nThird", 'Safe'], $contents);
        $html = view('filament.orders.existing-content', compact('contents'))->render();
        $this->assertStringContainsString('Line &lt;b&gt;', $html);
        $this->assertStringNotContainsString('<img', $html);
        $this->assertSame($before, $order->refresh()->getAttributes());
    }

    public function test_add_position_with_upload_preserves_existing_rows_and_uses_production_codes(): void
    {
        $this->mock(OrderInvoiceService::class)->shouldReceive('refreshDocument')->once()
            ->withArgs(fn (Orders $saved): bool => json_decode($saved->fresh()->items, true)[3]['name'] === 'Added'
                && (float) $saved->fresh()->price === 37.56)->andReturn('https://example.test/invoice.pdf');
        Storage::disk('uploads')->put('orders/keep.jpg', 'unchanged bytes');
        $order = Orders::findOrFail(1);
        $before = json_decode($order->items, true);
        $data = array_replace(app(OrderItemEditor::class)->newState(), [
            'name' => 'Added', 'price' => 7.555, 'size' => '40x60', 'manual_baget_code' => 'B2',
            'manual_express' => true, 'files' => [UploadedFile::fake()->image('new.png', 10, 10)],
        ]);
        $updated = app(OrderItemEditor::class)->add($order, $data);
        $items = json_decode($updated->items, true);
        $this->assertSame($before[0], $items[0]);
        $this->assertSame($before[2], $items[2]);
        $this->assertSame('Added', $items[3]['name']);
        $this->assertSame(7.56, $items[3]['price']);
        $this->assertSame('40x60', $items[3]['size']);
        $this->assertSame('B2', $items[3]['manual_baget_code']);
        $this->assertSame(1, $items[3]['is_manual_express']);
        $this->assertSame(37.56, (float) $updated->price);
        $this->assertSame(37.56, (float) $updated->sale_price);
        $this->assertSame(37.56, $items['totalPrice']);
        $this->assertSame(37.56, $items['saved_price']);
        $this->assertSame('7.56 €', $items['formatedTotalPrice']); // Original starts missing aggregate at zero.
        $path = ltrim(parse_url($items[3]['orig_images'][0], PHP_URL_PATH), '/');
        $this->assertStringStartsWith('orders/N1-3_', $path);
        $this->assertStringContainsString('_B2_', $path);
        Storage::disk('uploads')->assertExists($path);
        $this->assertSame('unchanged bytes', Storage::disk('uploads')->get('orders/keep.jpg'));
        $this->assertSame(5.0, (float) $updated->sale_eur);
        $this->assertSame(10.0, (float) $updated->sale_percent);
    }

    public function test_pdf_failure_when_adding_item_rolls_back_order_and_removes_new_upload_only(): void
    {
        Storage::disk('uploads')->put('orders/keep.jpg', 'existing bytes');
        $order = Orders::findOrFail(1);
        $before = $order->getRawOriginal();
        $this->mock(OrderInvoiceService::class)->shouldReceive('refreshDocument')->once()
            ->andThrow(new RuntimeException('PDF failed'));
        try {
            app(OrderItemEditor::class)->add($order, array_replace(app(OrderItemEditor::class)->newState(), [
                'name' => 'Rolled back', 'price' => 10, 'files' => [UploadedFile::fake()->image('new.png')],
            ]));
            $this->fail('PDF failure must reject adding the item.');
        } catch (RuntimeException $exception) {
            $this->assertSame('PDF failed', $exception->getMessage());
        }
        $this->assertSame($before, $order->fresh()->getRawOriginal());
        $this->assertSame(['orders/keep.jpg'], Storage::disk('uploads')->allFiles());
        $this->assertSame('existing bytes', Storage::disk('uploads')->get('orders/keep.jpg'));
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    public function test_pdf_failure_when_updating_item_rolls_back_all_totals_and_json(): void
    {
        $order = Orders::findOrFail(1);
        $before = $order->getRawOriginal();
        $this->mock(OrderInvoiceService::class)->shouldReceive('refreshDocument')->once()
            ->andThrow(new RuntimeException('PDF failed'));
        try {
            app(OrderItemEditor::class)->update($order, array_replace(app(OrderItemEditor::class)->state($order)[0], ['price' => 999]));
            $this->fail('PDF failure must reject updating the item.');
        } catch (RuntimeException $exception) {
            $this->assertSame('PDF failed', $exception->getMessage());
        }
        $this->assertSame($before, $order->fresh()->getRawOriginal());
    }

    public function test_attach_only_appends_to_selected_position_and_never_overwrites_colliding_files(): void
    {
        $this->mock(OrderInvoiceService::class)->shouldNotReceive('refreshDocument');
        Storage::disk('uploads')->put('orders/keep.jpg', 'unchanged');
        $order = Orders::findOrFail(1);
        $before = $order->getAttributes();
        $state = app(OrderItemEditor::class)->state($order)[1];
        $input = $state + ['files' => [UploadedFile::fake()->image('a.png', 10, 10)], 'price' => 999];
        $first = app(OrderItemEditor::class)->attach($order, $input);
        $items = json_decode($first->items, true);
        $firstPath = ltrim(parse_url($items[2]['orig_images'][0], PHP_URL_PATH), '/');
        $firstBytes = Storage::disk('uploads')->get($firstPath);
        $state = app(OrderItemEditor::class)->state($first)[1];
        $second = app(OrderItemEditor::class)->attach($first, $state + ['files' => [UploadedFile::fake()->image('a.png', 10, 10)]]);
        $items = json_decode($second->items, true);
        $this->assertCount(2, $items[2]['orig_images']);
        $this->assertNotSame($items[2]['orig_images'][0], $items[2]['orig_images'][1]);
        $this->assertSame($firstBytes, Storage::disk('uploads')->get($firstPath));
        $this->assertSame(json_decode($before['items'], true)[0], $items[0]);
        foreach (['price', 'sale_price', 'sale_eur', 'sale_percent', 'delivery', 'status'] as $field) {
            $this->assertSame($before[$field], $second->getRawOriginal($field));
        }
        $this->assertSame(20, $items[2]['price']);
        $this->assertSame('unchanged', Storage::disk('uploads')->get('orders/keep.jpg'));
    }

    public function test_attachment_batch_limit_is_atomic_and_preserves_existing_sources(): void
    {
        $order = Orders::findOrFail(1);
        $before = $order->getAttributes();
        $disk = Storage::disk('uploads');
        $disk->put('orders/keep.jpg', 'original source');
        $files = array_map(fn (int $index) => UploadedFile::fake()->image('source-'.$index.'.png', 10, 10), range(1, 31));
        $editor = app(OrderItemEditor::class);
        try {
            $editor->attach($order, $editor->state($order)[1] + ['files' => $files]);
            $this->fail('An oversized batch must be rejected before writing files.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('files', $exception->errors());
            $this->assertSame($before, $order->fresh()->getAttributes());
            $this->assertSame(['orders/keep.jpg'], $disk->allFiles());
        }
        $updated = $editor->attach($order, $editor->state($order)[1] + ['files' => array_slice($files, 0, 30)]);
        $items = json_decode($updated->items, true);
        $this->assertCount(30, $items[2]['orig_images']);
        $this->assertCount(30, array_unique($items[2]['orig_images']));
        $this->assertSame(json_decode($before['items'], true)[0], $items[0]);
        $this->assertSame('original source', $disk->get('orders/keep.jpg'));
        foreach ($items[2]['orig_images'] as $index => $url) {
            $this->assertSame(file_get_contents($files[$index]->getPathname()), $disk->get(ltrim(parse_url($url, PHP_URL_PATH), '/')));
        }
    }

    public function test_upload_failure_rolls_back_db_and_removes_only_new_files(): void
    {
        Storage::disk('uploads')->put('orders/keep.jpg', 'keep');
        $order = Orders::findOrFail(1);
        $before = $order->getAttributes();
        $files = [UploadedFile::fake()->image('new.png', 10, 10)];
        foreach (['add', 'attach'] as $operation) {
            $input = $operation === 'add' ? array_replace(app(OrderItemEditor::class)->newState(), ['name' => 'Added', 'files' => $files])
                : app(OrderItemEditor::class)->state($order)[0] + ['files' => $files];
            Orders::saving(fn () => throw new RuntimeException('Fixture save failure'));
            try {
                app(OrderItemEditor::class)->{$operation}($order, $input);
                $this->fail('Expected save failure.');
            } catch (RuntimeException $exception) {
                $this->assertSame('Fixture save failure', $exception->getMessage());
            } finally {
                Orders::flushEventListeners();
            }
            $this->assertSame($before, $order->refresh()->getAttributes());
            $this->assertSame(['orders/keep.jpg'], Storage::disk('uploads')->allFiles('orders'));
            $this->assertSame('keep', Storage::disk('uploads')->get('orders/keep.jpg'));
        }
    }

    public function test_invalid_upload_batch_is_rejected_before_any_files_or_order_are_written(): void
    {
        $order = Orders::findOrFail(1);
        $before = $order->getAttributes();
        $state = app(OrderItemEditor::class)->state($order)[0];
        $disguisedSource = UploadedFile::fake()->createWithContent('payload.txt', '<html>bad</html>');
        $disguisedImage = new UploadedFile($disguisedSource->getPathname(), 'bad.jpg', 'image/jpeg', null, true);
        foreach ([[], [UploadedFile::fake()->createWithContent('bad.php', '<?php echo 1;')],
            [$disguisedImage],
            [UploadedFile::fake()->image('good.png'), UploadedFile::fake()->create('big.png', 102401, 'image/png')]] as $files) {
            try {
                app(OrderItemEditor::class)->attach($order, $state + ['files' => $files]);
                $this->fail('Invalid upload accepted.');
            } catch (ValidationException) {
                $this->assertSame($before, $order->refresh()->getAttributes());
                $this->assertSame([], Storage::disk('uploads')->allFiles());
            }
        }
    }

    public function test_livewire_add_action_creates_new_card_without_saving_other_drafts(): void
    {
        $this->actingAs($this->actor(true), 'filament');
        $test = Livewire::test(OrderItemsFormHarness::class);
        $key = array_key_first($test->get('data.items_edit'));
        $test->set('data.items_edit.'.$key.'.name', 'Unsaved draft')
            ->mountAction(TestAction::make('addOrderItem')->schemaComponent('orderItemActions', 'form'))
            ->assertSchemaStateSet(['manual_canvas_id' => 2, 'manual_gift_code' => 'G0'])
            ->fillForm(['name' => 'New item', 'price' => 15, 'size' => '20x30', 'files' => [UploadedFile::fake()->image('new.png')]])
            ->callMountedAction()->assertHasNoErrors()
            ->assertSet('data.price', 45)->assertSet('data.sale_price', 45)
            ->assertSet('data.items_edit.'.$key.'.name', 'Unsaved draft');
        $items = json_decode(Orders::findOrFail(1)->items, true);
        $this->assertSame('First', $items[0]['name']);
        $this->assertSame('New item', $items[3]['name']);
        $this->assertCount(3, $test->get('data.items_edit'));
        $this->assertCount(1, $items[3]['orig_images']);
    }

    public function test_livewire_attach_action_keeps_item_draft_and_uploads_to_the_selected_card(): void
    {
        $this->actingAs($this->actor(true), 'filament');
        $test = Livewire::test(OrderItemsFormHarness::class);
        $keys = array_keys($test->get('data.items_edit'));
        $test->set('data.items_edit.'.$keys[1].'.name', 'Unsaved draft')
            ->mountAction(TestAction::make('attachOrderItemImages')->schemaComponent('items_edit', 'form')->arguments(['item' => $keys[1]]))
            ->fillForm(['files' => [UploadedFile::fake()->image('new.png')]])
            ->callMountedAction()->assertHasNoErrors()
            ->assertSet('data.items_edit.'.$keys[1].'.name', 'Unsaved draft');
        $items = json_decode(Orders::findOrFail(1)->items, true);
        $this->assertSame('Other', $items[2]['name']);
        $this->assertCount(1, $items[2]['orig_images']);
        $test->assertSee(basename($items[2]['orig_images'][0]));
        $test->callAction($this->action($keys[1]))->assertHasNoErrors();
        $this->assertSame('Unsaved draft', json_decode(Orders::findOrFail(1)->items, true)[2]['name']);
    }

    public function test_partial_second_file_failure_removes_entire_new_batch_and_preserves_old_file(): void
    {
        $order = Orders::findOrFail(1);
        $before = $order->getAttributes();
        $disk = Storage::disk('uploads');
        $disk->put('orders/keep.jpg', 'keep');
        $proxy = Mockery::mock(FilesystemAdapter::class);
        $proxy->shouldReceive('exists')->andReturnUsing(fn ($path) => $disk->exists($path));
        $proxy->shouldReceive('delete')->andReturnUsing(fn ($path) => $disk->delete($path));
        $writes = 0;
        $proxy->shouldReceive('putFileAs')->andReturnUsing(function ($folder, $file, $name) use ($disk, &$writes) {
            $writes++;
            if ($writes === 2) {
                $disk->put($folder.'/'.$name, 'partial');

                return false;
            }

            return $disk->putFileAs($folder, $file, $name);
        });
        Storage::shouldReceive('disk')->with('uploads')->andReturn($proxy);
        try {
            app(OrderItemEditor::class)->attach($order, app(OrderItemEditor::class)->state($order)[0] + [
                'files' => [UploadedFile::fake()->image('a.png'), UploadedFile::fake()->image('b.png')],
            ]);
            $this->fail('Expected write failure.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('files', $exception->errors());
            $this->assertSame(2, $writes);
            $this->assertSame($before, $order->refresh()->getAttributes());
            $this->assertSame(['orders/keep.jpg'], $disk->allFiles());
            $this->assertSame('keep', $disk->get('orders/keep.jpg'));
        }
    }

    public function test_permission_revoked_in_open_attach_modal_blocks_files_and_record_write(): void
    {
        $editor = $this->actor(true);
        $this->actingAs($editor, 'filament');
        $test = Livewire::test(OrderItemsFormHarness::class);
        $key = array_key_first($test->get('data.items_edit'));
        $test->mountAction(TestAction::make('attachOrderItemImages')->schemaComponent('items_edit', 'form')->arguments(['item' => $key]))
            ->fillForm(['files' => [UploadedFile::fake()->image('new.png')]]);
        $before = Orders::findOrFail(1)->getAttributes();
        DB::table('permission_role')->delete();
        $this->actingAs($editor->fresh(), 'filament');
        $test->callMountedAction();
        $this->assertSame($before, Orders::findOrFail(1)->getAttributes());
        $this->assertSame([], Storage::disk('uploads')->allFiles());
    }

    public function test_stale_attach_modal_displays_error_at_upload_field_without_writing_files(): void
    {
        $this->actingAs($this->actor(true), 'filament');
        $test = Livewire::test(OrderItemsFormHarness::class);
        $key = array_key_first($test->get('data.items_edit'));
        $test->mountAction(TestAction::make('attachOrderItemImages')->schemaComponent('items_edit', 'form')->arguments(['item' => $key]))
            ->fillForm(['files' => [UploadedFile::fake()->image('new.png')]]);
        $order = Orders::findOrFail(1);
        $items = json_decode($order->items, true);
        $items[0]['name'] = 'Concurrent';
        DB::table('orders')->where('id', 1)->update(['items' => json_encode($items)]);
        $test->callMountedAction()->assertHasErrors(['mountedActions.0.data.files']);
        $this->assertSame('Concurrent', json_decode($order->refresh()->items, true)[0]['name']);
        $this->assertSame([], Storage::disk('uploads')->allFiles());
    }

    public function test_model_veto_removes_new_uploads_and_does_not_report_success(): void
    {
        $order = Orders::findOrFail(1);
        $before = $order->getAttributes();
        foreach (['add', 'attach', 'update'] as $operation) {
            $input = $operation === 'add' ? array_replace(app(OrderItemEditor::class)->newState(), ['name' => 'Added'])
                : app(OrderItemEditor::class)->state($order)[0];
            $input['files'] = [UploadedFile::fake()->image('new.png')];
            Orders::saving(fn () => false);
            try {
                app(OrderItemEditor::class)->{$operation}($order, $input);
                $this->fail('A model veto reported success.');
            } catch (ValidationException) {
                $this->assertSame($before, $order->refresh()->getAttributes());
                $this->assertSame([], Storage::disk('uploads')->allFiles());
            } finally {
                Orders::flushEventListeners();
            }
        }
    }

    public function test_pdf_attachment_is_supported_but_new_item_image_picker_rejects_it(): void
    {
        $order = Orders::findOrFail(1);
        $source = UploadedFile::fake()->createWithContent('source', "%PDF-1.4\n%%EOF\n");
        $pdf = new UploadedFile($source->getPathname(), 'source.pdf', 'application/pdf', null, true);
        $updated = app(OrderItemEditor::class)->attach($order, app(OrderItemEditor::class)->state($order)[0] + ['files' => [$pdf]]);
        $items = json_decode($updated->items, true);
        $this->assertCount(2, $items[0]['orig_images']);
        $this->assertStringEndsWith('.pdf', $items[0]['orig_images'][1]);
        $before = $updated->getAttributes();
        try {
            app(OrderItemEditor::class)->add($updated, array_replace(app(OrderItemEditor::class)->newState(), ['name' => 'Added', 'files' => [$pdf]]));
            $this->fail('Document accepted by image-only new-item flow.');
        } catch (ValidationException) {
            $this->assertSame($before, $updated->refresh()->getAttributes());
            $this->assertCount(1, Storage::disk('uploads')->allFiles());
        }
    }

    public function test_temporary_upload_limit_matches_admin_source_fields(): void
    {
        $rules = FileUploadConfiguration::rules();
        $this->assertContains('max:102400', $rules);
        $this->assertTrue(validator(['file' => UploadedFile::fake()->create('source.psd', 20480)], ['file' => $rules])->passes());
        $this->assertTrue(validator(['file' => UploadedFile::fake()->create('source.psd', 102401)], ['file' => $rules])->fails());
        $this->assertSame('throttle:60,1', FileUploadConfiguration::middleware());
        $this->assertSame(5, FileUploadConfiguration::maxUploadTime());
        $this->assertTrue(FileUploadConfiguration::shouldCleanupOldUploads());
        $this->assertContains('jpg', config('livewire.temporary_file_upload.preview_mimes'));
    }

    public function test_original_source_formats_are_sniffed_and_attached_without_changing_bytes(): void
    {
        // Filament's bundled MIME detector maps .fig to application/x-xfig when the browser has no type.
        $this->assertContains('application/x-xfig', OrderItemUploads::acceptedTypes(true));
        $this->assertNotContains('application/x-xfig', OrderItemUploads::acceptedTypes(false));
        $sources = [
            'source.heic' => pack('N', 24).'ftypheic'.pack('N', 0).'mif1heic',
            'source.heif' => pack('N', 24).'ftypmif1'.pack('N', 0).'mif1heic',
            'source.psd' => '8BPS'.pack('n', 1).str_repeat("\0", 6).pack('nNNnn', 3, 1, 1, 8, 3).str_repeat("\0", 64),
            'source.fig' => 'fig-kiwi'.pack('V', 15).implode('', array_map('chr', range(128, 255))),
            'source-xfig.fig' => "#FIG 3.2  Produced by xfig version 3.2.5\nLandscape\nCenter\nMetric\nA4\n100.00\nSingle\n-2\n1200 2\n",
        ];
        $editor = app(OrderItemEditor::class);
        $order = Orders::findOrFail(1);
        foreach ($sources as $filename => $bytes) {
            $temporary = UploadedFile::fake()->createWithContent($filename, $bytes);
            // A real UploadedFile uses fileinfo rather than the fake's extension-based MIME.
            $file = new UploadedFile($temporary->getPathname(), $filename, null, null, true);
            $this->assertContains($file->getMimeType(), OrderItemUploads::acceptedTypes(true), $filename.': '.$file->getMimeType());
            $order = $editor->attach($order, $editor->state($order)[0] + ['files' => [$file]]);
            $images = json_decode($order->items, true)[0]['orig_images'];
            $path = ltrim(parse_url(end($images), PHP_URL_PATH), '/');
            $this->assertStringEndsWith('.'.pathinfo($filename, PATHINFO_EXTENSION), $path);
            $this->assertSame($bytes, Storage::disk('uploads')->get($path));
            if (in_array(pathinfo($filename, PATHINFO_EXTENSION), ['psd', 'fig'], true)) {
                $before = $order->getRawOriginal();
                $paths = Storage::disk('uploads')->allFiles();
                try {
                    $editor->add($order, array_replace($editor->newState(), ['name' => 'Rejected document', 'files' => [$file]]));
                    $this->fail('Image-only new-item flow accepted '.$filename);
                } catch (ValidationException) {
                    $this->assertSame($before, $order->fresh()->getRawOriginal());
                    $this->assertSame($paths, Storage::disk('uploads')->allFiles());
                }
            } else {
                $added = $editor->add($order, array_replace($editor->newState(), ['name' => 'HEIF image item', 'files' => [$file]]));
                $lastItem = collect(json_decode($added->items, true))->filter(fn ($row) => is_array($row) && ($row['name'] ?? '') === 'HEIF image item')->last();
                $imagePath = ltrim(parse_url($lastItem['orig_images'][0], PHP_URL_PATH), '/');
                $this->assertSame($bytes, Storage::disk('uploads')->get($imagePath));
                $order = $added;
            }
        }
        Http::assertNothingSent();
        Mail::assertNothingSent();
    }

    private function action(string $key): TestAction
    {
        return TestAction::make('updateOrderItem')->schemaComponent('items_edit', 'form')->arguments(['item' => $key]);
    }

    private function actor(bool $edit): User
    {
        DB::table('roles')->insert(['id' => 1, 'name' => 'fixture-editor']);
        DB::table('permissions')->insert(['id' => 1, 'key' => $edit ? 'edit_orders' : 'read_orders']);
        DB::table('permission_role')->insert(['role_id' => 1, 'permission_id' => 1]);
        DB::table('users')->insert(['id' => 1, 'role_id' => 1, 'email' => 'fixture@example.test']);

        return User::findOrFail(1);
    }

    private function order(): Orders
    {
        return Orders::query()->forceCreate([
            'items' => json_encode([
                0 => ['name' => 'First', 'price' => 10, 'sizeId' => '30x40', 'size_name' => 'Original size',
                    'userComment' => 'Old', 'terms_price' => 4, 'terms' => '4', 'orig_images' => ['orders/keep.jpg'],
                    'extension' => ['do' => 'not change']],
                2 => ['name' => 'Other', 'price' => 20, 'is_manual_express' => 1, 'terms_price' => 3],
                'totalPrice' => 30, 'saved_price' => 30, 'meta' => ['totalPrice' => 999],
            ]),
            'delivery' => json_encode(['country' => 'LV', 'address' => 'keep-address']),
            'price' => 30, 'sale_price' => 30,
        ]);
    }
}

class OrderItemsFormHarness extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public Orders $record;

    public array $data = [];

    public array $submitted = [];

    public function mount(): void
    {
        $this->record = Orders::findOrFail(1);
        $this->form->fill(['items_edit' => app(OrderItemEditor::class)->state($this->record)]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([OrderItemsForm::section()])->record($this->record)->statePath('data');
    }

    public function mainState(): void
    {
        $this->submitted = $this->form->getState();
    }

    public function render(): string
    {
        return '<div>{{ $this->form }}</div>';
    }
}
