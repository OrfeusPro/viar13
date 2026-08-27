<?php

namespace Tests\Feature\Admin;

use App\Models\Orders;
use App\Services\Admin\UpdateOrderVrNumberService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UpdateOrderVrNumberServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
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

        $service->update($order, 'BAW456');
        $this->assertDatabaseHas('vr_numbers', ['order_id' => $order->id, 'vrv_1' => null, 'vrv_2' => 456]);

        $service->update($order, '');
        $this->assertDatabaseMissing('vr_numbers', ['order_id' => $order->id]);
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
}
