<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Tables\OrdersTable;
use App\Models\OrderPaymentRequest;
use App\Models\Orders;
use App\Models\Role;
use App\Models\User;
use App\Models\VrNumber;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class OrderListRolePresentationTest extends TestCase
{
    private function asRole(string $name): void
    {
        Gate::before(fn (User $user, string $ability): ?bool => $ability === 'update' ? true : null);
        $user = new User;
        $user->id = 1;
        $user->setRelation('role', new Role(['name' => $name]));
        $this->actingAs($user, 'filament');
    }

    public function test_product_parameters_match_original_printing_visibility_including_gift_nominal(): void
    {
        $order = new Orders;
        $order->forceFill([
            'id' => 42, 'price' => 137, 'sale_price' => 120, 'sale_eur' => 5, 'sale_percent' => 10,
            'items' => json_encode([
                ['sumPrice' => 79, 'sumFormatedPrice' => 'POSITION-PRICE', 'name' => 'Fixture gift',
                    'pid' => 5, 'is_gift_card' => 1, 'price' => 79, 'gift_code' => 'FIXTURE-GIFT-CODE',
                    'sizeId' => '40x60', 'count' => 2, 'pack' => 'Fixture packing', 'terms' => 'Express 19€'],
                'total_terms_price' => 6,
            ]),
            'delivery' => json_encode(['deliv_price' => 4, 'coupon_type' => 'facebook', 'comment' => 'Fixture delivery note']),
        ]);
        $order->setRelation('user', new User(['email' => 'fixture@example.test']));
        foreach (['printing', 'admin', 'lite_manager'] as $role) {
            $this->asRole($role);
            $html = view('filament.tables.columns.order-product', ['getRecord' => fn () => $order])->render();
            foreach (['Fixture gift', '40x60', 'Fixture packing', 'FIXTURE-GIFT-CODE', '79€', 'Fixture delivery note'] as $visible) {
                $this->assertStringContainsString($visible, $html, $role.': '.$visible);
            }
            foreach (['Стоимость:', 'Итого:', 'POSITION-PRICE', 'requestReview', 'Express 19€', 'скриншот Facebook'] as $restricted) {
                if ($role === 'printing') {
                    $this->assertStringNotContainsString($restricted, $html);
                } else {
                    $this->assertStringContainsString($restricted, $html);
                }
            }
            $this->assertStringContainsString('Express', $html);
        }
    }

    private function paymentHtml(): string
    {
        $order = new Orders;
        $order->forceFill([
            'id' => 42, 'payment_status' => 'prepayment', 'prepayment_price' => 12.34,
            'created_at' => '2026-10-17 12:00:00', 'labels' => 'FIXTURE-LABEL',
        ]);

        return view('filament.tables.columns.order-payment', ['getRecord' => fn () => $order])->render();
    }

    public function test_printing_sees_payment_status_and_labels_without_payment_editing_controls(): void
    {
        $this->asRole('printing');
        $html = $this->paymentHtml();
        $this->assertStringContainsString('предоплата', $html);
        $this->assertStringContainsString('Создать этикетку Venipak для заказа №42', $html);
        $this->assertStringContainsString('FIXTURE-LABEL', $html);
        $this->assertStringContainsString('На печать', $html);
        $this->assertStringNotContainsString("save('payment_status'", $html);
        $this->assertStringNotContainsString("save('prepayment_price'", $html);
        $this->assertStringNotContainsString('12.34', $html);
        $page = new OrderHeaderRoleProbe;
        $this->assertFalse($page->createButtonIsVisible());
    }

    public function test_admin_and_lite_manager_keep_original_non_printing_controls(): void
    {
        foreach (['admin', 'lite_manager'] as $role) {
            $this->asRole($role);
            $html = $this->paymentHtml();
            $this->assertStringContainsString("save('payment_status'", $html);
            $this->assertStringContainsString("save('prepayment_price'", $html);
            $this->assertStringContainsString('12.34', $html);
            $this->assertTrue((new OrderHeaderRoleProbe)->createButtonIsVisible());
        }
    }

    public function test_printing_navigation_matches_original_without_changing_root_query_tab(): void
    {
        $this->asRole('printing');
        $page = new ListOrders;
        $tabs = $page->getTabs();
        $visible = array_filter($tabs, fn ($tab) => $tab->isVisible());
        $this->assertSame(['production', 'printing', 'sent_today', 'sent', 'new_recently', 'express'], array_keys($visible));
        $this->assertSame('Все текущие заказы', $tabs['express']->getLabel());
        $this->assertSame('current', $page->getDefaultActiveTab());
        $this->assertArrayHasKey('current', $tabs);
    }

    public function test_non_printing_navigation_retains_all_tabs_in_original_order(): void
    {
        foreach (['admin', 'lite_manager'] as $role) {
            $this->asRole($role);
            $tabs = (new ListOrders)->getTabs();
            $this->assertSame(
                ['current', 'completed', 'express', 'production', 'printing', 'sent_today', 'sent', 'new_recently', 'unpaid', 'all'],
                array_keys(array_filter($tabs, fn ($tab) => $tab->isVisible())),
            );
            $this->assertSame('Надо отправить сегодня', $tabs['express']->getLabel());
        }
    }

    private function numberHtml(bool $hasNumber = true): string
    {
        $order = new Orders;
        $order->forceFill(['id' => 42, 'has_pdf' => 1, 'pdf_approved' => 1]);
        $order->setRelation('vrNumber', $hasNumber ? new VrNumber(['vrv_1' => 123]) : null);
        $order->setRelation('order_payment_requests', collect([new OrderPaymentRequest([
            'public_number' => 'OPR-FIXTURE', 'amount' => 12.34, 'currency' => 'EUR',
            'status' => 'pending', 'token' => 'fixture-token', 'customer_country' => 'LV',
        ])]));

        return view('filament.tables.columns.order-number', ['getRecord' => fn () => $order])->render();
    }

    public function test_payment_request_cards_keep_original_labels_statuses_amount_and_safe_copy_url(): void
    {
        $this->asRole('admin');
        $methods = [
            'online_paysera' => 'Онлайн банкинг', 'creditcart' => 'Картой онлайн',
            'google_pay' => 'Google Pay', 'apple_pay' => 'Apple Pay',
            'paypalOnetimePayment' => 'PayPal', 'transfer' => 'Оплата перечислением', 'other' => 'other',
        ];
        foreach ($methods as $method => $label) {
            foreach (['pending' => 'Ожидает оплаты', 'paid' => 'Оплачено'] as $status => $statusLabel) {
                $order = (new Orders)->forceFill(['id' => 42]);
                $order->setRelation('vrNumber', null);
                $request = new OrderPaymentRequest([
                    'public_number' => 'OPR-000042', 'amount' => 12.5, 'currency' => 'EUR',
                    'purpose' => '<script>unsafe</script>', 'status' => $status,
                    'selected_payment_method' => $method, 'token' => 'fixture-token', 'customer_country' => 'PL',
                ]);
                $order->setRelation('order_payment_requests', collect([$request]));
                $html = view('filament.tables.columns.order-number', ['getRecord' => fn () => $order])->render();
                foreach (['OPR-000042', '12.50 EUR', 'Метод: '.$label, $statusLabel, 'adminPaymentRequestCopy()', 'Ссылка скопирована', 'Скопируйте ссылку вручную', $request->publicUrl()] as $expected) {
                    $this->assertStringContainsString($expected, $html);
                }
                $this->assertStringNotContainsString('<script>unsafe</script>', $html);
                $this->assertStringContainsString('&lt;script&gt;unsafe&lt;/script&gt;', $html);
            }
        }
    }

    public function test_printing_number_column_keeps_number_and_company_but_hides_invoice_and_payment_controls(): void
    {
        $this->asRole('printing');
        $html = $this->numberHtml();
        $this->assertStringContainsString('VR00123', $html);
        $this->assertStringContainsString('данные фирмы', $html);
        $this->assertStringContainsString('ViarStudia', $html);
        foreach (['manageVrNumber', 'updateOrderInvoiceFirm', 'generateInvoice', 'approveInvoice', 'createPaymentRequest', 'OPR-FIXTURE', '12.34', 'storage/pdf/42.pdf'] as $hidden) {
            $this->assertStringNotContainsString($hidden, $html);
        }
        $this->assertStringContainsString('Накладная заказа №42', $this->numberHtml(false));
    }

    public function test_non_printing_number_column_retains_financial_actions_and_existing_requests(): void
    {
        foreach (['admin', 'lite_manager'] as $role) {
            $this->asRole($role);
            $html = $this->numberHtml();
            foreach (["save('vr_number'", 'updateOrderInvoiceFirm', 'generateInvoice', 'approveInvoice', 'createPaymentRequest', 'OPR-FIXTURE', '12.34', 'storage/pdf/42.pdf'] as $visible) {
                $this->assertStringContainsString($visible, $html);
            }
            $this->assertStringContainsString('Накладная заказа №42', $this->numberHtml(false));
        }
    }

    private function userHtml(bool $hasUser = true): string
    {
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        if (! Schema::hasTable('user_types')) {
            Schema::create('user_types', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
            });
            DB::table('user_types')->insert(['id' => 1, 'name' => 'Новый']);
            Schema::create('a_order_from', function (Blueprint $table): void {
                $table->id();
                $table->string('title');
            });
            DB::table('a_order_from')->insert(['id' => 1, 'title' => 'Fixture channel']);
        }
        $user = new User;
        $user->forceFill([
            'id' => 2, 'first_name' => 'Fixture', 'last_name' => 'Client', 'email' => 'client@example.test',
            'phone' => '123456', 'client_status' => 1, 'locale' => 'lv', 'pdf_locale' => 'ru', 'orders_count' => 3,
        ]);
        $order = new Orders;
        $order->forceFill([
            'id' => 42, 'user_id' => 2, 'a_order_from' => 1, 'catid' => 0,
            'delivery' => json_encode(['first_name' => 'Fallback', 'email' => 'fallback@example.test']),
        ]);
        $order->setRelation('user', $hasUser ? $user : null);
        $order->setRelation('manager', null);

        return view('filament.tables.columns.order-user', ['getRecord' => fn () => $order])->render();
    }

    public function test_printing_keeps_client_contacts_locale_and_status_but_hides_sales_segment(): void
    {
        $this->asRole('printing');
        $html = $this->userHtml();
        foreach (['Fixture Client', 'client@example.test', '123456', 'Заказов было: 3', "save('pdf_locale'", "save('client_status'"] as $visible) {
            $this->assertStringContainsString($visible, $html);
        }
        foreach (['Канал продаж:', 'Категория:', 'Менеджер:', 'Fixture channel'] as $hidden) {
            $this->assertStringNotContainsString($hidden, $html);
        }
        $missing = $this->userHtml(false);
        $this->assertStringContainsString('Fallback', $missing);
        $this->assertStringContainsString('fallback@example.test', $missing);
        $this->assertStringNotContainsString("save('pdf_locale'", $missing);
    }

    public function test_admin_and_lite_manager_keep_sales_segment(): void
    {
        foreach (['admin', 'lite_manager'] as $role) {
            $this->asRole($role);
            $html = $this->userHtml();
            foreach (['Канал продаж:', 'Категория:', 'Менеджер:', "save('pdf_locale'", "save('client_status'"] as $visible) {
                $this->assertStringContainsString($visible, $html);
            }
        }
    }

    public function test_printing_cannot_expose_restricted_alternative_columns(): void
    {
        Schema::create('user_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
        DB::table('user_types')->insert(['id' => 1, 'name' => 'Новый']);
        foreach (['printing', 'admin', 'lite_manager'] as $role) {
            $this->asRole($role);
            $table = OrdersTable::configure(Table::make(Mockery::mock(HasTable::class)));
            foreach (['vr_number', 'manager.email', 'a_order_from', 'catid', 'sale_price', 'painter_endtime'] as $name) {
                $this->assertSame($role !== 'printing', $table->getColumn($name)->isVisible(), $role.': '.$name);
            }
            foreach (['number_controls', 'user_controls', 'payment_controls', 'artist_controls', 'created_at', 'printingAssignment.user.email'] as $name) {
                $this->assertTrue($table->getColumn($name)->isVisible(), $role.': '.$name);
            }
        }
    }
}

class OrderHeaderRoleProbe extends ListOrders
{
    public function createButtonIsVisible(): bool
    {
        return $this->getHeaderActions()[0]->isVisible();
    }
}
