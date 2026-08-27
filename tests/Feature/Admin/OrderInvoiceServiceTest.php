<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\DynamicPDFController;
use App\Models\Orders;
use App\Services\Admin\OrderInvoiceService;
use App\Services\BestEffortMailService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class OrderInvoiceServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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

        $pdf = Mockery::mock(DynamicPDFController::class);
        $pdf->shouldReceive('getPDFFromOrder')->twice()->andReturn('https://viar13.loc/storage/pdf/'.$order->id.'.pdf');
        $this->app->instance(DynamicPDFController::class, $pdf);

        $mail = Mockery::mock(BestEffortMailService::class);
        $mail->shouldReceive('send')->twice()->andReturnTrue();
        $this->app->instance(BestEffortMailService::class, $mail);

        $service = app(OrderInvoiceService::class);
        $first = $service->approve($order);
        $second = $service->approve($order);

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
