<?php

namespace Tests\Feature\Invoice;

use App\Http\Controllers\GiftcardController;
use App\Mail\GiftCard;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Stevebauman\Location\Facades\Location;
use Tests\TestCase;

class GiftCardDocumentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Http::preventStrayRequests();
        Mail::fake();
        Location::shouldReceive('get')->andReturnFalse();
        $background = file_get_contents(public_path('theme/viar/images/gift/giftcard.png'));
        $disk = Storage::fake('gift-document');
        app()->usePublicPath($disk->path('public'));
        $disk->put('public/theme/viar/images/gift/giftcard.png', $background);
        Schema::create('coupons', function (Blueprint $table): void {
            $table->id();
            $table->integer('order_id');
            $table->string('text');
            $table->string('pdf');
        });
        DB::table('coupons')->insert([
            ['id' => 1, 'order_id' => 42, 'text' => 'LOCAL-GIFT-ONE', 'pdf' => 'http://127.0.0.1/gift-one.pdf'],
            ['id' => 2, 'order_id' => 42, 'text' => 'LOCAL-GIFT-TWO', 'pdf' => 'http://127.0.0.1/gift-two.pdf'],
            ['id' => 3, 'order_id' => 99, 'text' => 'OTHER-ORDER', 'pdf' => 'http://127.0.0.1/other-order.pdf'],
        ]);
        Schema::create('a_mail_top_sale', function (Blueprint $table): void {
            $table->id();
            $table->string('image');
            $table->string('size');
            $table->integer('price');
            $table->integer('sale_price');
        });
        DB::table('a_mail_top_sale')->insert([
            ['id' => 1, 'image' => 'fixture-one.jpg', 'size' => '40x60', 'price' => 50, 'sale_price' => 0],
            ['id' => 2, 'image' => 'fixture-two.jpg', 'size' => '60x80', 'price' => 80, 'sale_price' => 70],
        ]);
        Schema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->integer('foreign_key');
            foreach (['table_name', 'column_name', 'locale', 'value'] as $key) {
                $table->string($key);
            }
        });
        foreach (['ru', 'en', 'lv', 'lt', 'pl', 'de', 'ee'] as $locale) {
            foreach ([1, 2] as $id) {
                DB::table('translations')->insert(['foreign_key' => $id, 'table_name' => 'a_mail_top_sale', 'column_name' => 'title', 'locale' => $locale, 'value' => 'Fixture offer '.$id]);
            }
        }
        DB::connection()->getPdo()->sqliteCreateFunction('RAND', fn () => 0.5);
    }

    public static function locales(): array
    {
        return array_map(fn ($locale) => [$locale], ['ru', 'en', 'lv', 'lt', 'pl', 'de', 'ee']);
    }

    #[DataProvider('locales')]
    public function test_gift_pdf_renders_real_background_amount_and_code_as_one_page(string $locale): void
    {
        $controller = app(GiftcardController::class);
        $url = $controller->generateGiftCardPDF(2, 42, 1, 50, $locale, 'LOCAL-GIFT-ONE');
        $this->assertSame(url('/storage/pdf-gift-card/42_2_1.pdf'), $url);
        $pdf = Storage::disk('gift-document')->get('public/storage/pdf-gift-card/42_2_1.pdf');
        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertSame(1, preg_match_all('~/Type\s*/Page\b~', $pdf));
        $this->assertStringContainsString('/Subtype /Image', $pdf);
        $html = (new \ReflectionMethod($controller, 'getGiftCardHtml'))->invoke($controller, 50, 'LOCAL-GIFT-ONE');
        $this->assertStringContainsString('LOCAL-GIFT-ONE', $html);
        $this->assertStringContainsString(trans('gl.gift_card', [], $locale), $html);
        $this->assertStringNotContainsString('>gl.gift_card<', $html);
        $this->assertStringContainsString($locale === 'ru' ? '50&#8364;' : '50 EUR', $html);
        $this->assertStringContainsString('data:image/png;base64,', $html);
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    #[DataProvider('locales')]
    public function test_gift_email_renders_only_this_orders_pdf_links_and_escapes_client_name(string $locale): void
    {
        app()->setLocale($locale);
        $mail = new GiftCard(['subject' => 'Fixture gift', 'first_name' => '<script>unsafe</script>', 'order_id' => 42], $locale);
        $html = $mail->render();
        foreach (['http://127.0.0.1/gift-one.pdf', 'http://127.0.0.1/gift-two.pdf', '&lt;script&gt;unsafe&lt;/script&gt;', 'Fixture offer 1', 'Fixture offer 2'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        $this->assertStringNotContainsString('other-order.pdf', $html);
        $this->assertStringNotContainsString('<script>unsafe</script>', $html);
        $this->assertStringNotContainsString('gift_card.mail_text3', $html);
        $this->assertSame(3, DB::table('coupons')->count());
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    public function test_single_certificate_email_has_one_download_link_without_list_numbering(): void
    {
        DB::table('coupons')->where('id', 2)->delete();
        app()->setLocale('ru');
        $html = (new GiftCard(['subject' => 'Fixture gift', 'first_name' => 'Тест', 'order_id' => 42], 'ru'))->render();
        $this->assertSame(1, substr_count($html, 'href="http://127.0.0.1/gift-one.pdf"'));
        $this->assertStringNotContainsString('gift-two.pdf', $html);
        $this->assertStringNotContainsString('other-order.pdf', $html);
        $this->assertDoesNotMatchRegularExpression('/>\s*1\.\s*Скачать/u', $html);
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }
}
