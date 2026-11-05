<?php

namespace Tests\Feature\Admin;

use App\Models\Orders;
use App\Models\VrNumber;
use App\Services\Admin\OrderInvoiceService;
use App\Services\Admin\UpdateOrderVrNumberService;
use App\Services\Invoice\InvoicePdfWriter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class UpdateOrderVrNumberServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Mail::fake();
        Http::preventStrayRequests();
        $disk = Storage::fake('vr-pdf-test');
        app()->usePublicPath($disk->path('public'));
        $this->mock(OrderInvoiceService::class)->shouldReceive('refreshDocument')->andReturnUsing(function (Orders $order): string {
            app(InvoicePdfWriter::class)->write(public_path('storage/pdf/'.$order->id.'.pdf'), '%PDF '.Orders::getVRById($order->id));

            return url('/storage/pdf/'.$order->id.'.pdf');
        })->byDefault();

        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->boolean('has_pdf')->nullable()->default(true);
            $table->boolean('pdf_approved')->default(true);
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
    }

    public function test_it_creates_replaces_and_removes_vr_number(): void
    {
        $order = Orders::findOrFail(DB::table('orders')->insertGetId([
            'created_at' => now(),
            'updated_at' => now(),
        ]));
        $service = app(UpdateOrderVrNumberService::class);

        $service->update($order, 'VR00123');
        $this->assertDatabaseHas('vr_numbers', ['order_id' => $order->id, 'vrv_1' => 123]);
        $this->assertSame('%PDF VR00123', file_get_contents(public_path('storage/pdf/'.$order->id.'.pdf')));

        $service->update($order, 'BAW456');
        $this->assertDatabaseHas('vr_numbers', ['order_id' => $order->id, 'vrv_1' => null, 'vrv_2' => 456]);
        $this->assertSame('%PDF BAW456', file_get_contents(public_path('storage/pdf/'.$order->id.'.pdf')));

        $service->update($order, '');
        $this->assertDatabaseMissing('vr_numbers', ['order_id' => $order->id]);
        $this->assertNull($order->fresh()->has_pdf);
        $this->assertTrue((bool) $order->fresh()->pdf_approved);
        $this->assertFileDoesNotExist(public_path('storage/pdf/'.$order->id.'.pdf'));
    }

    public function test_it_rejects_unknown_number_format(): void
    {
        $order = Orders::findOrFail(DB::table('orders')->insertGetId([
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        $this->expectException(ValidationException::class);
        app(UpdateOrderVrNumberService::class)->update($order, 'INVALID-123');
    }

    public static function series(): array
    {
        return [['VR00', 'vrv_1', 'VR004'], ['BAW', 'vrv_2', 'BAW4'], ['VRR445', 'vrv_3', 'VRR445004'], ['DS020', 'vrv_4', 'DS0204']];
    }

    #[DataProvider('series')]
    public function test_automatic_number_uses_latest_row_of_selected_series_and_refreshes_pdf(string $prefix, string $column, string $expected): void
    {
        $order = Orders::query()->forceCreate([]);
        DB::table('vr_numbers')->insert(['order_id' => 991, $column => 900]);
        DB::table('vr_numbers')->insert(['order_id' => 992, $column => 3]);
        app(UpdateOrderVrNumberService::class)->generate($order, $prefix);
        $this->assertSame($expected, Orders::getVRById($order->id));
        $this->assertSame('%PDF '.$expected, file_get_contents(public_path('storage/pdf/'.$order->id.'.pdf')));
        $this->assertTrue((bool) $order->fresh()->pdf_approved);
    }

    public function test_rollback_restores_deleted_number_flag_and_document(): void
    {
        $order = Orders::query()->forceCreate([]);
        $service = app(UpdateOrderVrNumberService::class);
        $service->update($order, 'BAW7');
        try {
            DB::transaction(function () use ($order, $service): void {
                $service->update($order, '');
                $this->assertFileDoesNotExist(public_path('storage/pdf/'.$order->id.'.pdf'));
                throw new RuntimeException('Later database error');
            });
        } catch (RuntimeException $exception) {
            $this->assertSame('Later database error', $exception->getMessage());
        }
        $this->assertSame('BAW7', Orders::getVRById($order->id));
        $this->assertTrue((bool) $order->fresh()->has_pdf);
        $this->assertSame('%PDF BAW7', file_get_contents(public_path('storage/pdf/'.$order->id.'.pdf')));
    }

    public function test_pdf_failure_rolls_back_number_replacement_and_previous_pdf(): void
    {
        $order = Orders::query()->forceCreate([]);
        $service = app(UpdateOrderVrNumberService::class);
        $service->update($order, 'BAW7');
        $this->mock(OrderInvoiceService::class)->shouldReceive('refreshDocument')->once()->andReturnUsing(function (Orders $order): void {
            app(InvoicePdfWriter::class)->write(public_path('storage/pdf/'.$order->id.'.pdf'), '%PDF failed replacement');
            throw new RuntimeException('PDF failed');
        });
        try {
            $service->update($order, 'VR008');
            $this->fail('PDF failure must reject number replacement.');
        } catch (RuntimeException $exception) {
            $this->assertSame('PDF failed', $exception->getMessage());
        }
        $this->assertSame('BAW7', Orders::getVRById($order->id));
        $this->assertSame('%PDF BAW7', file_get_contents(public_path('storage/pdf/'.$order->id.'.pdf')));
    }

    public function test_first_automatic_number_cannot_be_created_twice_for_same_order(): void
    {
        $order = Orders::query()->forceCreate([]);
        $service = app(UpdateOrderVrNumberService::class);
        $service->generate($order, 'VRR445');
        $this->assertSame('VRR445001', Orders::getVRById($order->id));
        try {
            $service->generate($order, 'BAW');
            $this->fail('Stale create must not replace an existing number.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('prefix', $exception->errors());
        }
        $this->assertSame(1, VrNumber::count());
        $this->assertSame('VRR445001', Orders::getVRById($order->id));
    }

    public function test_series_names_use_original_order_strings_source(): void
    {
        Schema::create('order_strings', function (Blueprint $table): void {
            $table->id();
            foreach (['consignor_text', 'consignor_text_vrv', 'consignor_text_vrr', 'consignor_text_vra'] as $field) {
                $table->string($field);
            }
        });
        DB::table('order_strings')->insert([
            'id' => 1, 'consignor_text' => 'Seller one', 'consignor_text_vrv' => 'Seller two',
            'consignor_text_vrr' => 'Seller three', 'consignor_text_vra' => 'Seller four',
        ]);
        $this->assertSame(['VR00' => 'Seller one', 'BAW' => 'Seller two', 'VRR445' => 'Seller three', 'DS020' => 'Seller four'], UpdateOrderVrNumberService::prefixOptions());
    }

    public function test_pdf_delete_failure_rolls_back_number_and_invoice_flag(): void
    {
        $order = Orders::query()->forceCreate([]);
        $service = app(UpdateOrderVrNumberService::class);
        $service->update($order, 'BAW7');
        $this->mock(InvoicePdfWriter::class)->shouldReceive('remove')->once()
            ->andThrow(new RuntimeException('Delete failed'));
        try {
            $service->update($order, '');
            $this->fail('Failed deletion must reject number removal.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Delete failed', $exception->getMessage());
        }
        $this->assertSame('BAW7', Orders::getVRById($order->id));
        $this->assertTrue((bool) $order->fresh()->has_pdf);
        $this->assertSame('%PDF BAW7', file_get_contents(public_path('storage/pdf/'.$order->id.'.pdf')));
    }

    public function test_model_veto_does_not_create_number_or_pdf(): void
    {
        $order = Orders::query()->forceCreate([]);
        VrNumber::saving(fn () => false);
        try {
            app(UpdateOrderVrNumberService::class)->generate($order, 'BAW');
            $this->fail('Saving veto must reject number creation.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('number', $exception->errors());
        } finally {
            app('events')->forget('eloquent.saving: '.VrNumber::class);
        }
        $this->assertSame(0, VrNumber::count());
        $this->assertFileDoesNotExist(public_path('storage/pdf/'.$order->id.'.pdf'));
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }
}
