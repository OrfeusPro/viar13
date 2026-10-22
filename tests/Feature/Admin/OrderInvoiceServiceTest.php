<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\DynamicPDFController;
use App\Models\Orders;
use App\Models\User;
use App\Services\Admin\OrderInvoiceService;
use App\Services\BestEffortMailService;
use App\Services\Invoice\InvoicePdfWriter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class OrderInvoiceServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Http::preventStrayRequests();
        Mail::fake();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('email');
            $table->string('locale')->nullable();
            $table->string('invited')->nullable();
            $table->decimal('bonuses', 10, 2)->default(0);
            $table->string('inv_sale_code')->nullable();
            $table->boolean('is_active_friend_inv')->default(false);
            $table->string('last_ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id');
            $table->text('items')->nullable();
            $table->text('delivery')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('status')->default('watching');
            $table->boolean('has_pdf')->default(false);
            $table->boolean('pdf_approved')->default(false);
            $table->string('approved_date')->nullable();
            $table->string('pdf_link')->nullable();
            $table->timestamps();
        });
        Schema::create('vr_numbers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->unique();
            $table->unsignedBigInteger('vrv_1')->nullable();
            $table->unsignedBigInteger('vrv_2')->nullable();
            $table->unsignedBigInteger('vrv_3')->nullable();
            $table->unsignedBigInteger('vrv_4')->nullable();
            $table->timestamps();
        });
        Schema::create('stocks', function (Blueprint $table): void {
            $table->id();
            $table->decimal('friend_sale', 10, 2)->default(0);
        });
        Schema::create('user_messages', function (Blueprint $table): void {
            $table->id();
            $table->timestamps();
        });
    }

    public function test_it_generates_invoice_and_marks_order_only_after_pdf_is_created(): void
    {
        $order = $this->createOrder();
        DB::table('vr_numbers')->insert([
            'order_id' => $order->id,
            'vrv_1' => 123,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pdf = Mockery::mock(DynamicPDFController::class);
        $pdf->shouldReceive('getPDFFromOrder')->once()->andReturn('https://viar13.loc/storage/pdf/'.$order->id.'.pdf');
        $this->app->instance(DynamicPDFController::class, $pdf);

        app(OrderInvoiceService::class)->generate($order);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'has_pdf' => 1,
            'pdf_link' => 'https://viar13.loc/storage/pdf/'.$order->id.'.pdf',
        ]);
    }

    public function test_it_approves_sends_mail_and_applies_referral_bonus_only_once(): void
    {
        DB::table('stocks')->insert(['id' => 1, 'friend_sale' => 5]);
        $inviterId = DB::table('users')->insertGetId([
            'email' => 'inviter@example.test',
            'bonuses' => 10,
            'inv_sale_code' => 'INVITE01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $customerId = DB::table('users')->insertGetId([
            'email' => 'customer@example.test',
            'locale' => 'ru',
            'invited' => 'INVITE01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $order = $this->createOrder($customerId, ['has_pdf' => 1]);
        config()->set('admin_migration.invoice_email_enabled', true);

        $pdf = Mockery::mock(DynamicPDFController::class);
        $pdf->shouldReceive('getPDFFromOrder')->twice()->andReturn('https://viar13.loc/storage/pdf/'.$order->id.'.pdf');
        $this->app->instance(DynamicPDFController::class, $pdf);

        $mail = Mockery::mock(BestEffortMailService::class);
        $mail->shouldReceive('send')->twice()->andReturnTrue();
        $this->app->instance(BestEffortMailService::class, $mail);

        $service = app(OrderInvoiceService::class);
        $staleOrder = $order->fresh();
        $first = $service->approve($order);
        $second = $service->approve($staleOrder);

        $this->assertTrue($first['mail_sent']);
        $this->assertFalse($first['reapproved']);
        $this->assertTrue($second['reapproved']);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'pegging',
            'pdf_approved' => 1,
        ]);
        $this->assertSame(15.0, (float) DB::table('users')->where('id', $inviterId)->value('bonuses'));
        $this->assertNull(DB::table('users')->where('id', $customerId)->value('invited'));
    }

    public function test_it_does_not_mark_invoice_when_pdf_generation_fails(): void
    {
        $order = $this->createOrder();
        DB::table('vr_numbers')->insert([
            'order_id' => $order->id,
            'vrv_2' => 456,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pdf = Mockery::mock(DynamicPDFController::class);
        $pdf->shouldReceive('getPDFFromOrder')->once()->andThrow(new RuntimeException('PDF failed'));
        $this->app->instance(DynamicPDFController::class, $pdf);

        try {
            app(OrderInvoiceService::class)->generate($order);
            $this->fail('RuntimeException was not thrown.');
        } catch (RuntimeException) {
            $this->assertDatabaseHas('orders', [
                'id' => $order->id,
                'has_pdf' => 0,
                'pdf_link' => null,
            ]);
        }
    }

    public function test_external_invoice_email_is_logged_instead_of_sent_by_default(): void
    {
        config()->set('admin_migration.invoice_email_enabled', false);
        $order = $this->createOrder(attributes: ['has_pdf' => 1]);

        $pdf = Mockery::mock(DynamicPDFController::class);
        $pdf->shouldReceive('getPDFFromOrder')->once()->andReturn('https://viar13.loc/storage/pdf/'.$order->id.'.pdf');
        $this->app->instance(DynamicPDFController::class, $pdf);

        $mail = Mockery::mock(BestEffortMailService::class);
        $mail->shouldReceive('attempt')->once()->andReturnTrue();
        $mail->shouldNotReceive('send');
        $this->app->instance(BestEffortMailService::class, $mail);

        $result = app(OrderInvoiceService::class)->approve($order);

        $this->assertTrue($result['email_suppressed']);
        $this->assertTrue($result['mail_logged']);
        $this->assertFalse($result['mail_sent']);
    }

    public function test_edit_refresh_uses_current_order_and_vr_without_changing_invoice_flags(): void
    {
        $order = $this->createOrder(attributes: ['items' => '[{"name":"Old"}]']);
        DB::table('orders')->where('id', $order->id)->update([
            'items' => '[{"name":"Updated","price":75}]', 'price' => 75,
            'delivery' => '{"address":"New address"}',
        ]);
        DB::table('vr_numbers')->insert(['order_id' => $order->id, 'vrv_1' => 123]);
        $before = $order->fresh()->getRawOriginal();
        $pdf = Mockery::mock(DynamicPDFController::class);
        $pdf->shouldReceive('getPDFFromOrder')->once()->withArgs(function (array $data, $vr, $firm): bool {
            $this->assertSame('Updated', $data['items'][0]['name']);
            $this->assertSame('New address', $data['delivery']['address']);
            $this->assertSame('75 €', $data['price']);
            $this->assertNotEmpty($vr);
            $this->assertNull($firm);

            return true;
        })->andReturn('https://example.test/invoice.pdf');
        $this->app->instance(DynamicPDFController::class, $pdf);
        $this->assertSame('https://example.test/invoice.pdf', app(OrderInvoiceService::class)->refreshDocument($order));
        $this->assertSame($before, $order->fresh()->getRawOriginal());
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    public static function failedInvoiceSaves(): array
    {
        return [
            'generation' => ['generate', 'order', 1],
            'approval flag' => ['approve', 'order', 1],
            'approval link' => ['approve', 'order', 2],
            'inviter bonus' => ['approve', 'inviter', 1],
            'customer invitation' => ['approve', 'customer', 1],
        ];
    }

    #[DataProvider('failedInvoiceSaves')]
    public function test_failed_save_rolls_back_invoice_and_referral_without_sending_mail(string $operation, string $target, int $saveNumber): void
    {
        DB::table('stocks')->insert(['id' => 1, 'friend_sale' => 5]);
        $inviter = User::findOrFail(DB::table('users')->insertGetId(['email' => 'inviter@example.test', 'bonuses' => 10, 'inv_sale_code' => 'INVITE01']));
        $customer = User::findOrFail(DB::table('users')->insertGetId(['email' => 'customer@example.test', 'locale' => 'ru', 'invited' => 'INVITE01']));
        $order = $this->createOrder($customer->id, ['has_pdf' => 1]);
        $disk = Storage::fake('invoice-rollback-test');
        $disk->put('invoice.pdf', '%PDF original');
        $before = $order->getRawOriginal();
        $saves = 0;
        $event = $target === 'order' ? 'eloquent.saving: '.Orders::class : 'eloquent.saving: '.User::class;
        $targetId = match ($target) {
            'order' => $order->id,
            'inviter' => $inviter->id,
            default => $customer->id,
        };
        $listener = function ($model) use ($targetId, $saveNumber, &$saves) {
            if ($model->id === $targetId && ++$saves === $saveNumber) {
                return false;
            }
        };
        app('events')->listen($event, $listener);
        $pdf = Mockery::mock(DynamicPDFController::class);
        if ($operation === 'approve' && $target === 'order' && $saveNumber === 1) {
            $pdf->shouldNotReceive('getPDFFromOrder');
        } else {
            $pdf->shouldReceive('getPDFFromOrder')->once()->andReturnUsing(function () use ($disk): string {
                app(InvoicePdfWriter::class)->write($disk->path('invoice.pdf'), '%PDF replacement');

                return 'https://example.test/invoice.pdf';
            });
        }
        $this->app->instance(DynamicPDFController::class, $pdf);
        $mail = Mockery::mock(BestEffortMailService::class);
        $mail->shouldNotReceive('send');
        $mail->shouldNotReceive('attempt');
        $this->app->instance(BestEffortMailService::class, $mail);
        try {
            app(OrderInvoiceService::class)->{$operation}($order);
            $this->fail('Saving veto must reject the invoice action.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('invoice', $exception->errors());
        } finally {
            app('events')->forget($event);
        }
        $this->assertSame($before, $order->fresh()->getRawOriginal());
        $this->assertEquals(10, $inviter->fresh()->bonuses);
        $this->assertSame('INVITE01', $inviter->fresh()->inv_sale_code);
        $this->assertSame('INVITE01', $customer->fresh()->invited);
        $this->assertSame('%PDF original', $disk->get('invoice.pdf'));
        $this->assertSame(['invoice.pdf'], $disk->allFiles());
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    private function createOrder(?int $userId = null, array $attributes = []): Orders
    {
        $userId ??= DB::table('users')->insertGetId([
            'email' => 'customer-'.uniqid().'@example.test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $id = DB::table('orders')->insertGetId(array_merge([
            'user_id' => $userId,
            'items' => '[]',
            'delivery' => '{}',
            'price' => 100,
            'status' => 'watching',
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes));

        return Orders::findOrFail($id);
    }
}
