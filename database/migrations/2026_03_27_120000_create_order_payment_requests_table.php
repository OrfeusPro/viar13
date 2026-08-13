<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderPaymentRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('order_payment_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('order_id')->index();
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->unsignedInteger('created_by')->nullable()->index();
            $table->string('public_number')->nullable()->unique();
            $table->string('token')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->string('purpose')->nullable();
            $table->string('status')->default('pending')->index();
            $table->string('selected_payment_method')->nullable();
            $table->string('billing_invoice_uuid')->nullable()->index();
            $table->string('customer_email')->nullable();
            $table->string('customer_first_name')->nullable();
            $table->string('customer_last_name')->nullable();
            $table->string('customer_country', 8)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_payment_requests');
    }
}
