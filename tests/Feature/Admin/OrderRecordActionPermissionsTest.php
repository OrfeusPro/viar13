<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Orders\Tables\OrdersTable;
use App\Mail\Payment_successful;
use App\Models\OrderPaymentRequest;
use App\Models\Orders;
use App\Models\User;
use App\Models\VrNumber;
use App\Services\Admin\OrderAdminChatService;
use App\Services\Admin\OrderClientChatService;
use App\Services\Admin\OrderInvoiceService;
use App\Services\Admin\OrderPainterChatService;
use App\Services\Admin\OrderPaymentService;
use App\Services\Admin\OrderRecipientEmailService;
use App\Services\Admin\OrderReviewRequestService;
use App\Services\Admin\OrderUserService;
use App\Services\Admin\UpdateOrderVrNumberService;
use App\Services\Payment\OrderPaymentRequestService;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Testing\TestAction;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\Livewire;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OrderRecordActionPermissionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Http::preventStrayRequests();
        Mail::fake();
        Notification::fake();
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->integer('role_id');
            $table->string('email');
            $table->string('pdf_locale')->nullable();
            $table->string('locale')->nullable();
            $table->integer('client_status')->nullable();
        });
        foreach (['roles' => 'name', 'permissions' => 'key'] as $name => $column) {
            Schema::create($name, function (Blueprint $table) use ($column): void {
                $table->id();
                $table->string($column);
            });
        }
        foreach (['permission_role' => ['permission_id', 'role_id'], 'user_roles' => ['user_id', 'role_id']] as $name => $columns) {
            Schema::create($name, function (Blueprint $table) use ($columns): void {
                foreach ($columns as $column) {
                    $table->integer($column);
                }
            });
        }
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->decimal('prepayment_price', 10, 2)->default(0);
            $table->string('payment_status');
            $table->boolean('has_pdf')->default(false);
            $table->boolean('pdf_approved')->default(false);
        });
        Schema::create('order_admin_comments', function (Blueprint $table): void {
            $table->id();
            $table->integer('orders_id');
            $table->integer('user_id');
            $table->text('comment');
            $table->timestamps();
        });
        Schema::create('user_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
        DB::table('user_types')->insert(['id' => 1, 'name' => 'Новый']);
        Schema::create('vr_numbers', function (Blueprint $table): void {
            $table->id();
            $table->integer('order_id');
            foreach (range(1, 4) as $index) {
                $table->integer('vrv_'.$index)->nullable();
            }
        });
        DB::table('roles')->insert(['id' => 1, 'name' => 'fixture-editor']);
        DB::table('permissions')->insert(['id' => 1, 'key' => 'edit_orders']);
        DB::table('users')->insert(['id' => 1, 'role_id' => 1, 'email' => 'fixture@example.test']);
        DB::table('users')->insert(['id' => 2, 'role_id' => 1, 'email' => 'customer@example.test', 'locale' => 'lv', 'pdf_locale' => 'lv', 'client_status' => 1]);
        DB::table('orders')->insert(['id' => 1, 'user_id' => 2, 'price' => 10, 'payment_status' => 'not_payed']);
    }

    public function test_inline_fields_save_directly_and_preserve_unrelated_order_data(): void
    {
        config(['admin_migration.payment_notifications_enabled' => false, 'admin_migration.order_status_notifications_enabled' => false]);
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('status')->nullable();
            $table->text('delivery')->nullable();
            $table->timestamp('status_date')->nullable();
            $table->timestamp('pegging_date')->nullable();
            $table->timestamps();
        });
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamps();
            foreach (['last_ip', 'registration_page', 'referrer_url', 'utm_parameters', 'user_agent'] as $field) {
                $table->text($field)->nullable();
            }
        });
        DB::table('permission_role')->insert(['permission_id' => 1, 'role_id' => 1]);
        DB::table('user_types')->insert(['id' => 2, 'name' => 'Постоянный']);
        DB::table('orders')->where('id', 1)->update(['status' => 'watching', 'delivery' => '{"country":"LV","extension":"keep"}']);
        $this->actingAs(User::findOrFail(1), 'filament');
        $order = Orders::findOrFail(1);
        $component = Livewire::test(OrderRecordPermissionsHarness::class);
        foreach (['payment_status' => 'prepayment', 'prepayment_price' => '12.34', 'status' => 'pegging', 'delivery_date' => '2026-10-28', 'pdf_locale' => 'ru', 'client_status' => '2'] as $field => $value) {
            $component->callAction(TestAction::make('updateOrderInlineField')->table($order)->arguments(compact('field', 'value')))
                ->assertHasNoErrors()->assertNotified('Изменение сохранено');
        }
        $this->assertDatabaseHas('orders', ['id' => 1, 'status' => 'pegging', 'payment_status' => 'prepayment', 'prepayment_price' => 12.34, 'price' => 10]);
        $this->assertSame(['country' => 'LV', 'extension' => 'keep', 'when_send' => '2026-10-28'], json_decode($order->fresh()->delivery, true));
        $this->assertDatabaseHas('users', ['id' => 2, 'pdf_locale' => 'ru', 'client_status' => 2]);
        $before = $order->fresh()->getAttributes();
        $component->callAction(TestAction::make('updateOrderInlineField')->table($order)->arguments(['field' => 'price', 'value' => '999']))
            ->assertNotified('Изменение не сохранено');
        $this->assertSame($before, $order->fresh()->getAttributes());
        DB::table('permission_role')->delete();
        $this->actingAs(User::findOrFail(1), 'filament');
        Livewire::test(OrderRecordPermissionsHarness::class)->assertActionHidden(TestAction::make('updateOrderInlineField')->table($order));
        // The disabled-notification branch renders Payment_successful through the log mailer.
        Mail::assertSent(Payment_successful::class, 1);
        Mail::assertSentCount(1);
        Http::assertNothingSent();
    }

    public function test_inline_vr_fields_dispatch_without_a_modal_and_printing_cannot_change_finances(): void
    {
        DB::table('permission_role')->insert(['permission_id' => 1, 'role_id' => 1]);
        $this->actingAs(User::findOrFail(1), 'filament');
        $service = $this->mock(UpdateOrderVrNumberService::class);
        $service->shouldReceive('generate')->once()->withArgs(fn ($order, $prefix): bool => $order->id === 1 && $prefix === 'BAW')
            ->andReturn(new VrNumber(['vrv_2' => 1]));
        $service->shouldReceive('update')->once()->withArgs(fn ($order, $number): bool => $order->id === 1 && $number === 'BAW123')->andReturnNull();
        $service->shouldReceive('update')->once()->withArgs(fn ($order, $number): bool => $order->id === 1 && $number === null)->andReturnNull();
        $order = Orders::findOrFail(1);
        foreach ([['vr_prefix', 'BAW'], ['vr_number', 'BAW123'], ['vr_number', null]] as [$field, $value]) {
            Livewire::test(OrderRecordPermissionsHarness::class)
                ->callAction(TestAction::make('updateOrderInlineField')->table($order)->arguments(compact('field', 'value')))
                ->assertHasNoErrors()->assertNotified('Изменение сохранено');
        }
        DB::table('roles')->where('id', 1)->update(['name' => 'printing']);
        $this->actingAs(User::findOrFail(1), 'filament');
        $this->mock(OrderPaymentService::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('updateStatus');
            $mock->shouldNotReceive('updatePrepayment');
        });
        foreach (['payment_status' => 'payed', 'prepayment_price' => '10', 'vr_number' => 'BAW124', 'vr_prefix' => 'BAW'] as $field => $value) {
            Livewire::test(OrderRecordPermissionsHarness::class)
                ->call('mountTableAction', 'updateOrderInlineField', '1', compact('field', 'value'))
                ->assertForbidden();
        }
        $this->assertDatabaseHas('orders', ['id' => 1, 'payment_status' => 'not_payed', 'prepayment_price' => 0]);
        Http::assertNothingSent();
        Mail::assertNothingSent();
    }

    public function test_inline_firm_validates_and_passes_only_requisites_to_invoice_service(): void
    {
        DB::table('permission_role')->insert(['permission_id' => 1, 'role_id' => 1]);
        $this->actingAs(User::findOrFail(1), 'filament');
        $data = ['name' => 'Fixture Company', 'reg_num' => '123', 'addr' => 'Fixture street', 'bank' => 'Fixture Bank',
            'vat_num' => 'LV123', 'bank_code' => 'ABC', 'office_addr' => 'Office', 'acc_num' => 'LV000'];
        $this->mock(OrderInvoiceService::class)->shouldReceive('generate')->once()
            ->withArgs(fn ($order, $firm): bool => $order->id === 1 && $firm === $data)->andReturn('https://example.test/invoice.pdf');
        $order = Orders::findOrFail(1);
        $before = $order->getRawOriginal();
        $action = fn (array $arguments) => TestAction::make('updateOrderInvoiceFirm')->table($order)->arguments($arguments);
        Livewire::test(OrderRecordPermissionsHarness::class)->callAction($action($data + ['price' => 999]))
            ->assertHasNoErrors()->assertNotified('Данные фирмы сохранены в PDF-счёте');
        foreach ([['name' => ''], ['addr' => ''], ['bank' => str_repeat('x', 256)]] as $invalid) {
            Livewire::test(OrderRecordPermissionsHarness::class)->callAction($action(array_replace($data, $invalid)))
                ->assertNotified('Данные фирмы не сохранены');
        }
        $this->assertSame($before, $order->fresh()->getRawOriginal());
        DB::table('roles')->where('id', 1)->update(['name' => 'printing']);
        $this->actingAs(User::findOrFail(1), 'filament');
        Livewire::test(OrderRecordPermissionsHarness::class)->assertActionHidden($action($data));
        DB::table('roles')->where('id', 1)->update(['name' => 'fixture-editor']);
        DB::table('permission_role')->delete();
        $this->actingAs(User::findOrFail(1), 'filament');
        Livewire::test(OrderRecordPermissionsHarness::class)->assertActionHidden($action($data));
        Http::assertNothingSent();
        Mail::assertNothingSent();
    }

    public function test_firm_disclosure_renders_all_eight_fields_with_safe_values_and_without_edit_modal(): void
    {
        DB::table('permission_role')->insert(['permission_id' => 1, 'role_id' => 1]);
        $this->actingAs(User::findOrFail(1), 'filament');
        $firm = ['name' => 'Fixture "Company" <script>', 'reg_num' => '123', 'addr' => 'Fixture street', 'bank' => 'Bank',
            'vat_num' => 'LV123', 'bank_code' => 'ABC', 'office_addr' => 'Office', 'acc_num' => 'LV000'];
        $this->mock(OrderInvoiceService::class)->shouldReceive('defaultFirmData')->once()->andReturn($firm);
        $order = Orders::findOrFail(1);
        $order->setRelation('vrNumber', new VrNumber(['vrv_1' => 123]));
        $order->setRelation('order_payment_requests', collect());
        $html = view('filament.tables.columns.order-number', ['getRecord' => fn () => $order])->render();
        foreach (array_keys($firm) as $field) {
            $this->assertStringContainsString('x-model="firm.'.$field.'"', $html);
        }
        $this->assertStringContainsString('updateOrderInvoiceFirm', $html);
        $this->assertStringNotContainsString("mountTableAction('editInvoiceFirm'", $html);
        $this->assertStringNotContainsString('<script>', $html);
        Http::assertNothingSent();
        Mail::assertNothingSent();
    }

    public function test_payment_request_action_creates_real_request_without_mutating_order(): void
    {
        Schema::create('order_payment_requests', function (Blueprint $table): void {
            $table->id();
            foreach (['order_id', 'user_id', 'created_by'] as $field) {
                $table->integer($field)->nullable();
            }
            foreach (['public_number', 'token', 'currency', 'purpose', 'status', 'customer_email', 'customer_first_name', 'customer_last_name', 'customer_country'] as $field) {
                $table->string($field)->nullable();
            }
            $table->decimal('amount', 10, 2);
            $table->timestamps();
        });
        DB::table('permission_role')->insert(['permission_id' => 1, 'role_id' => 1]);
        $this->actingAs(User::findOrFail(1), 'filament');
        $order = Orders::findOrFail(1);
        $before = $order->getRawOriginal();
        foreach (range(1, 3) as $index) {
            app(OrderPaymentRequestService::class)->createForOrder($order, ['amount' => $index]);
        }
        $action = TestAction::make('createPaymentRequest')->table($order);
        $component = Livewire::test(OrderRecordPermissionsHarness::class, ['renderNumber' => true])
            ->assertSee('OPR-000001')->assertSee('OPR-000003')
            ->mountAction($action)->assertActionMounted($action)
            ->assertFormSet(['amount' => null, 'purpose' => null], 'mountedActionSchema0')
            ->callMountedAction()->assertHasFormErrors(['amount' => 'required']);
        $this->assertDatabaseCount('order_payment_requests', 3);
        $component
            ->fillForm(['amount' => 0, 'purpose' => 'Fixture'])
            ->callMountedAction()->assertHasFormErrors(['amount' => 'min']);
        $this->assertDatabaseCount('order_payment_requests', 3);
        $component->fillForm(['amount' => 12.5, 'purpose' => '  Shipping  '])->callMountedAction()
            ->assertHasNoErrors()->assertNotified('Заявка на оплату создана')
            ->assertSee('OPR-000004')->assertSee('Shipping')->assertDontSee('OPR-000001');
        $request = OrderPaymentRequest::query()->latest('id')->firstOrFail();
        $this->assertSame('12.50', $request->amount);
        $this->assertSame('Shipping', $request->purpose);
        $this->assertSame('OPR-000004', $request->public_number);
        $this->assertSame('customer@example.test', $request->customer_email);
        $this->assertEquals(1, $request->created_by);
        $this->assertSame($before, $order->fresh()->getRawOriginal());
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    public function test_vr_actions_receive_selected_series_and_explicit_delete_arguments(): void
    {
        DB::table('permission_role')->insert(['permission_id' => 1, 'role_id' => 1]);
        $this->actingAs(User::findOrFail(1), 'filament');
        $service = $this->mock(UpdateOrderVrNumberService::class);
        $service->shouldReceive('generate')->once()->withArgs(fn ($order, $prefix): bool => $order->id === 1 && $prefix === 'DS020')
            ->andReturn(new VrNumber(['vrv_4' => 1]));
        $service->shouldReceive('update')->once()->withArgs(fn ($order, $number): bool => $order->id === 1 && $number === null)->andReturnNull();
        $order = Orders::findOrFail(1);
        $create = TestAction::make('createVrNumber')->table($order)->arguments(['prefix' => 'DS020']);
        Livewire::test(OrderRecordPermissionsHarness::class)->mountAction($create)->assertActionMounted($create)
            ->assertFormSet(['prefix' => 'DS020'], 'mountedActionSchema0')->callMountedAction()->assertHasNoErrors()->assertNotified('Накладная создана');
        $delete = TestAction::make('manageVrNumber')->table($order)->arguments(['delete' => true]);
        Livewire::test(OrderRecordPermissionsHarness::class)->mountAction($delete)->assertActionMounted($delete)
            ->assertFormSet(['number' => null], 'mountedActionSchema0')->callMountedAction()->assertHasNoErrors()->assertNotified('Номер накладной обновлён');
        Http::assertNothingSent();
        Mail::assertNothingSent();
    }

    #[DataProvider('financialAccessChanges')]
    public function test_open_financial_actions_stop_before_services_after_access_changes(string $change): void
    {
        $this->mock(OrderInvoiceService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('defaultFirmData')->once()->andReturn(['name' => 'Fixture', 'addr' => 'Fixture street']);
            $mock->shouldNotReceive('generate');
            $mock->shouldNotReceive('approve');
        });
        $this->mock(OrderPaymentService::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('updateStatus');
            $mock->shouldNotReceive('updatePrepayment');
        });
        $this->mock(UpdateOrderVrNumberService::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('update');
            $mock->shouldNotReceive('generate');
        });
        $this->mock(OrderPaymentRequestService::class, fn (MockInterface $mock) => $mock->shouldNotReceive('createForOrder'));
        $cases = [
            'createVrNumber' => ['prefix' => 'BAW'],
            'manageVrNumber' => ['number' => 'VR00123'],
            'createPaymentRequest' => ['amount' => 10, 'purpose' => 'Fixture'],
            'editInvoiceFirm' => ['name' => 'Fixture', 'addr' => 'Fixture street'],
            'generateInvoice' => [], 'approveInvoice' => [],
            'updatePaymentStatus' => ['payment_status' => 'payed'],
            'updatePrepayment' => ['prepayment_price' => 5],
        ];
        foreach ($cases as $name => $data) {
            DB::table('roles')->where('id', 1)->update(['name' => 'fixture-editor']);
            DB::table('permission_role')->delete();
            DB::table('permission_role')->insert(['permission_id' => 1, 'role_id' => 1]);
            $this->actingAs(User::findOrFail(1), 'filament');
            $order = Orders::findOrFail(1);
            $action = TestAction::make($name)->table($order);
            $component = Livewire::test(OrderRecordPermissionsHarness::class)->mountAction($action)->assertActionMounted($action);
            if ($data !== []) {
                $component->fillForm($data);
            }
            if ($change === 'printing') {
                DB::table('roles')->where('id', 1)->update(['name' => 'printing']);
            } else {
                DB::table('permission_role')->delete();
            }
            $this->actingAs(User::findOrFail(1), 'filament');
            // Printing retains edit_orders for production work; finance has a separate restriction.
            $this->assertSame($change === 'printing', auth('filament')->user()->can('update', $order));
            $component->callMountedAction();
            Livewire::test(OrderRecordPermissionsHarness::class)->assertActionHidden($action)
                ->call('mountTableAction', $name, '1');
            $this->assertDatabaseHas('orders', ['id' => 1, 'payment_status' => 'not_payed', 'prepayment_price' => 0, 'has_pdf' => 0, 'pdf_approved' => 0]);
            $this->assertDatabaseCount('vr_numbers', 0);
        }
        Http::assertNothingSent();
        Mail::assertNothingSent();
    }

    public static function financialAccessChanges(): array
    {
        return ['edit permission revoked' => ['revoked'], 'changed to printing with edit permission' => ['printing']];
    }

    public function test_chat_replies_and_read_commands_stop_before_services_after_permission_revocation(): void
    {
        $this->mock(OrderAdminChatService::class, fn (MockInterface $mock) => $mock->shouldNotReceive('add'));
        foreach ([OrderClientChatService::class, OrderPainterChatService::class] as $service) {
            $this->mock($service, function (MockInterface $mock): void {
                $mock->shouldNotReceive('send');
                $mock->shouldNotReceive('markMessageAsRead');
            });
        }
        foreach ([
            'sendClientChatMessage' => ['thread_type' => 'general', 'comment' => 'Fixture client draft'],
            'sendPainterChatMessage' => ['comment' => 'Fixture painter draft'],
            'manageAdminChat' => ['comment' => 'Fixture internal draft'],
        ] as $name => $data) {
            DB::table('permission_role')->insert(['permission_id' => 1, 'role_id' => 1]);
            $this->actingAs(User::findOrFail(1), 'filament');
            $order = Orders::findOrFail(1);
            $action = TestAction::make($name)->table($order);
            $component = Livewire::test(OrderRecordPermissionsHarness::class)
                ->mountAction($action)->assertActionMounted($action)->fillForm($data);
            DB::table('permission_role')->delete();
            $this->actingAs(User::findOrFail(1), 'filament');
            $this->assertFalse(auth('filament')->user()->can('update', $order));
            $component->callMountedAction();
            Livewire::test(OrderRecordPermissionsHarness::class)->assertActionHidden($action)
                ->call('mountTableAction', $name, '1');
        }
        // Read commands have no form: a forged invocation must also stop before the service.
        foreach (['readClientChatMessage', 'readPainterChatMessage'] as $name) {
            $action = TestAction::make($name)->table(Orders::findOrFail(1));
            Livewire::test(OrderRecordPermissionsHarness::class)->assertActionHidden($action)
                ->call('mountTableAction', $name, '1', ['message_id' => 123]);
        }
        $this->assertDatabaseCount('order_admin_comments', 0);
        Http::assertNothingSent();
        Mail::assertNothingSent();
        Notification::assertNothingSent();
    }

    public function test_open_customer_actions_stop_before_services_after_edit_permission_revocation(): void
    {
        $this->mock(OrderUserService::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('updatePdfLocale');
            $mock->shouldNotReceive('updateClientStatus');
        });
        $this->mock(OrderRecipientEmailService::class, fn (MockInterface $mock) => $mock->shouldNotReceive('send'));
        $this->mock(OrderReviewRequestService::class, fn (MockInterface $mock) => $mock->shouldNotReceive('send'));
        foreach ([
            'updateUserPdfLocale' => ['pdf_locale' => 'ru'],
            'updateClientStatus' => ['client_status' => 1],
            'composeRecipientEmail' => ['subject' => 'Fixture', 'greetings' => 'Hello', 'line' => 'Fixture message', 'salutation' => 'Bye'],
            'requestReview' => [],
        ] as $name => $data) {
            DB::table('permission_role')->insert(['permission_id' => 1, 'role_id' => 1]);
            $this->actingAs(User::findOrFail(1), 'filament');
            $order = Orders::findOrFail(1);
            $action = TestAction::make($name)->table($order);
            $component = Livewire::test(OrderRecordPermissionsHarness::class)->mountAction($action)->assertActionMounted($action);
            if ($data !== []) {
                $component->fillForm($data);
            }
            DB::table('permission_role')->delete();
            $this->actingAs(User::findOrFail(1), 'filament');
            $this->assertFalse(auth('filament')->user()->can('update', $order));
            $component->callMountedAction();
            Livewire::test(OrderRecordPermissionsHarness::class)->assertActionHidden($action)
                ->call('mountTableAction', $name, '1');
            $this->assertDatabaseHas('users', ['id' => 2, 'pdf_locale' => 'lv', 'client_status' => 1]);
        }
        Notification::assertNothingSent();
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }
}

class OrderRecordPermissionsHarness extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions, InteractsWithSchemas, InteractsWithTable;

    public bool $renderNumber = false;

    public function table(Table $table): Table
    {
        $production = OrdersTable::configure(Table::make($this));
        $actions = array_map(fn (string $name) => $production->getAction($name), [
            'updateOrderInlineField', 'updateOrderInvoiceFirm', 'createVrNumber', 'manageVrNumber', 'createPaymentRequest', 'editInvoiceFirm', 'generateInvoice', 'approveInvoice', 'updatePaymentStatus', 'updatePrepayment',
            'updateUserPdfLocale', 'updateClientStatus', 'composeRecipientEmail', 'requestReview',
            'sendClientChatMessage', 'sendPainterChatMessage', 'manageAdminChat', 'readClientChatMessage', 'readPainterChatMessage',
        ]);

        return $table->query($this->renderNumber
            ? Orders::query()->with(['vrNumber', 'order_payment_requests' => fn ($query) => $query->latest('id')->limit(3)])
            : Orders::query())
            ->columns($this->renderNumber ? [$production->getColumn('number_controls')] : [TextColumn::make('id')])
            ->recordActions($actions);
    }

    public function render(): string
    {
        return '<div>{{ $this->table }}</div>';
    }
}
