<?php

namespace Tests\Feature\Invoice;

use App\Http\Controllers\DynamicPDFController;
use App\Models\OrderString;
use App\Services\Admin\UpdateOrderService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class InvoiceDocumentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Http::preventStrayRequests();
        Mail::fake();
        $logo = file_get_contents(public_path('pdf_print_logo.png'));
        $disk = Storage::fake('invoice-business-test');
        app()->usePublicPath($disk->path('public'));
        $disk->put('public/pdf_print_logo.png', $logo);
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->json('settings')->nullable();
            $table->string('pdf_locale')->nullable();
        });
        Schema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->string('table_name');
            $table->string('column_name');
            $table->integer('foreign_key');
            $table->string('locale');
            $table->text('value');
        });
        $columns = array_unique([
            ...(new OrderString)->getTranslatableAttributes(), 'invoice', 'ur_name_l', 'prn_nr_place',
            'bank_code_place_vrv', 'bank_code_place_vrr', 'bank_code_place_vra',
            'is_pvn', 'is_pvn_vrv', 'is_pvn_vrr', 'is_pvn_vra', 'nds',
        ]);
        Schema::create('order_strings', function (Blueprint $table) use ($columns): void {
            $table->id();
            foreach ($columns as $column) {
                $table->string($column)->nullable();
            }
        });
        $values = array_fill_keys($columns, '');
        foreach (['' => 'ViarStudia', '_vrv' => 'ViarCanvas', '_vrr' => 'Viar Riga', '_vra' => 'Viar Artist'] as $suffix => $seller) {
            $values['consignor_text'.$suffix] = $seller;
            $values['req_num_text'.$suffix] = 'REG-'.$seller;
            $values['pvn_text'.$suffix] = 'VAT-'.$seller;
            $values['bank_name_text'.$suffix] = 'Fixture Bank';
            $values['acc_num_text'.$suffix] = 'LV00FIXTURE';
            $values['addr_text'.$suffix] = 'Seller fixture address';
        }
        DB::table('order_strings')->insert(array_replace($values, [
            'id' => 1, 'nds' => 21, 'is_pvn' => 1, 'is_pvn_vrv' => 1, 'is_pvn_vrr' => 0, 'is_pvn_vra' => 1,
            'deliv_price' => 'Delivery fixture',
        ]));
        DB::table('users')->insert(['id' => 1, 'settings' => '{"locale":"ru"}', 'pdf_locale' => 'en']);
    }

    private function order(): array
    {
        return [
            ...array_fill_keys(UpdateOrderService::COMPANY_FIELDS, ''),
            'id' => 123, 'user_id' => 1, 'approved_date' => '17.10.2026',
            'price' => '121 €', 'sale_price' => 121, 'sale_eur' => 10, 'sale_percent' => 10,
            'ur_name' => 'on', 'ur_name_l' => 'Fixture Customer Ltd', 'ur_reg_num' => 'REG-CLIENT',
            'ur_legal_addr' => 'Legal fixture street', 'ur_pnr_nr' => 'VAT-CLIENT',
            'ur_bank_name' => 'Client Bank', 'ur_bank_code' => 'CLIENTSWIFT', 'ur_bank_acc_code' => 'LV00CLIENT',
            'delivery' => ['first_name' => 'Test', 'last_name' => 'Buyer', 'address' => 'Delivery fixture street', 'deliv_price' => 6.05],
            'items' => [
                ['name' => 'Canvas fixture', 'sumPrice' => 121, 'count' => 2, 'size_name' => '40x60', 'terms_price' => 12.1],
                'totalPrice' => 121,
            ],
        ];
    }

    public static function sellers(): array
    {
        return [
            'default VAT' => ['VR00123', null, 'ViarStudia', true],
            'BAW VAT' => ['BAW123', null, 'ViarCanvas', true],
            'VRR non VAT' => ['VRR123', null, 'Viar Riga', false],
            'DS020 VAT' => ['DS020123', null, 'Viar Artist', true],
            'custom non VAT' => ['BAW123', '', 'Custom seller', false],
            'custom VAT' => ['VRR123', 'CUSTOM-VAT', 'Custom seller', true],
        ];
    }

    #[DataProvider('sellers')]
    public function test_full_document_seller_items_delivery_discounts_and_vat_match_original(string $vr, ?string $vat, string $seller, bool $hasVat): void
    {
        $firm = $vat === null ? null : [
            'name' => 'Custom seller', 'reg_num' => 'CUSTOM-REG', 'addr' => 'Custom address',
            'vat_num' => $vat, 'bank' => 'Custom bank', 'bank_code' => 'CUSTOMSWIFT',
            'office_addr' => 'Custom office', 'acc_num' => 'CUSTOMACCOUNT',
        ];
        $html = app(DynamicPDFController::class)->getOrderDataInHtml($this->order(), $vr, $firm);
        foreach ([$vr, $seller, '17.10.2026', 'Fixture Customer Ltd', 'REG-CLIENT', 'Legal fixture street',
            'VAT-CLIENT', 'Client Bank', 'CLIENTSWIFT', 'LV00CLIENT', 'Delivery fixture street',
            'Canvas fixture 40x60', 'Canvas fixture Express', 'Delivery fixture', '-21.1', '118.05&euro;'] as $expected) {
            $this->assertStringContainsString($expected, $html);
        }
        if ($hasVat) {
            $this->assertStringContainsString('Total excluding VAT', $html);
            $this->assertStringContainsString('24.15&euro;', $html);
            $this->assertStringContainsString('50.00</td>', $html);
        } else {
            $this->assertStringNotContainsString('Total excluding VAT', $html);
            $this->assertStringContainsString('60.50</td>', $html);
        }
        $this->assertSame(1, DB::table('users')->count());
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    public function test_full_document_renders_to_pdf_without_network_or_working_files(): void
    {
        $url = app(DynamicPDFController::class)->getPDFFromOrder($this->order(), 'VR00123');
        $this->assertSame(url('/storage/pdf/123.pdf'), $url);
        $pdf = Storage::disk('invoice-business-test')->get('public/storage/pdf/123.pdf');
        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertStringContainsString('%%EOF', $pdf);
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    private function longOrder(): array
    {
        $order = $this->order();
        $order['items'] = [];
        foreach (range(1, 60) as $number) {
            $order['items'][] = [
                'name' => sprintf('LONG-ITEM-%03d', $number).' Canvas with a descriptive product title',
                'sumPrice' => 12.1, 'count' => 1, 'size_name' => '40x60', 'terms_price' => 0,
            ];
        }
        $order['price'] = 726;
        $order['sale_price'] = 726;
        $order['sale_eur'] = 0;
        $order['sale_percent'] = 0;

        return $order;
    }

    public function test_long_invoice_keeps_all_items_and_total_in_a_real_multipage_pdf(): void
    {
        $order = $this->longOrder();
        $controller = app(DynamicPDFController::class);
        $html = $controller->getOrderDataInHtml($order, 'VR00123', null);
        foreach (range(1, 60) as $number) {
            $this->assertSame(1, substr_count($html, sprintf('LONG-ITEM-%03d', $number)));
        }
        $this->assertStringContainsString('732.05&euro;', $html);
        $controller->getPDFFromOrder($order, 'VR00123');
        $pdf = Storage::disk('invoice-business-test')->get('public/storage/pdf/123.pdf');
        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertGreaterThan(1, preg_match_all('~/Type\s*/Page\b~', $pdf));
        $this->assertStringContainsString('%%EOF', $pdf);
        $this->assertSame(1, DB::table('users')->count());
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    public static function documentLanguages(): array
    {
        return [
            ['ru', 'Сумма к оплате'], ['en', 'Amount payable'], ['lv', 'Kopējā summa'],
            ['lt', 'Mokėtina suma'], ['pl', 'Kwota do zapłaty'], ['de', 'Zahlbetrag'], ['ee', 'Tasumisele kuuluv summa'],
        ];
    }

    #[DataProvider('documentLanguages')]
    public function test_pdf_language_override_applies_to_entire_document(string $locale, string $totalLabel): void
    {
        DB::table('users')->where('id', 1)->update([
            'settings' => json_encode(['locale' => $locale === 'ru' ? 'en' : 'ru']), 'pdf_locale' => $locale,
        ]);
        $html = app(DynamicPDFController::class)->getOrderDataInHtml($this->order(), 'VR00123', null);
        $this->assertStringContainsString($totalLabel, strip_tags($html));
        $this->assertStringContainsString('118.05&euro;', $html);
        $this->assertSame(0, preg_match('/(?:invoice|ord_strings|cart_new)\.[a-z_]+/', strip_tags($html)));
        $this->assertStringContainsString('Fixture Customer Ltd', $html);
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }
}
