<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait CreatesSaLeadFixtures
{
    use CreatesSaChatSchema;
    use CreatesSaServiceFixtures;

    protected function createSaLeadFixtures(): void
    {
        $this->createSaChatSchema();
        $this->createSaServiceFixtures();
        Schema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->string('table_name'); $table->string('column_name'); $table->integer('foreign_key');
            $table->string('locale'); $table->text('value'); $table->timestamps();
        });
        Schema::table('orders', function (Blueprint $table): void {
            foreach (['sale_price', 'order_image', 'photo', 'ur_name', 'ur_name_l', 'ur_reg_num', 'ur_legal_addr', 'ur_pnr_nr', 'ur_bank_name',
                'ur_bank_code', 'ur_bank_acc_code', 'is_ur', 'billing_invoice_uuid', 'painter_images', 'painter_sketch_images'] as $field) {
                $table->text($field)->nullable();
            }
            $table->integer('use_bonus')->default(0);
        });
        Schema::table('users', function (Blueprint $table): void {
            $table->string('address')->nullable(); $table->string('postal_index')->nullable(); $table->string('city')->nullable();
            $table->integer('type_id')->nullable();
            $table->decimal('bonuses', 10, 2)->default(0);
            $table->integer('is_30_40')->default(0);
            $table->integer('active_coupon')->nullable();
            $table->integer('is_coupon_dates')->nullable();
            $table->integer('is_active_friend_inv')->nullable();
            $table->text('used_universal_coupon')->nullable();
        });
        Schema::create('string_tranlations', function (Blueprint $table): void {
            $table->id(); $table->text('key')->nullable();
            $table->text('standart_text')->nullable(); $table->text('express_text')->nullable();
            $table->integer('standart_price')->default(0); $table->integer('express_price')->default(5);
            foreach (['lv', 'ru', 'en', 'uk', 'ee', 'lt', 'pl', 'de'] as $locale) { $table->text($locale)->nullable(); }
        });
        DB::table('string_tranlations')->insert(['standart_text' => 'Standard production', 'express_text' => 'Express production', 'standart_price' => 0, 'express_price' => 5]);
        Schema::create('order_action', function (Blueprint $table): void {
            $table->id(); $table->text('user'); $table->text('activity'); $table->timestamps();
        });
        Schema::create('a_production_time', function (Blueprint $table): void {
            $table->id(); $table->string('category');
            $table->text('standart_text'); $table->text('express_text');
            $table->integer('standart_price'); $table->integer('express_price');
        });
        foreach (['canvas', 'collage'] as $category) {
            DB::table('a_production_time')->insert(['category' => $category, 'standart_text' => 'Standard production',
                'express_text' => 'Express production', 'standart_price' => 0, 'express_price' => 5]);
        }
        Schema::create('coupons', function (Blueprint $table): void {
            $table->id();
            foreach (['text', 'value', 'sale_date', 'pdf'] as $field) { $table->text($field)->nullable(); }
            foreach (['is_30_40_free', 'is_dates_sale', 'is_universal', 'is_active', 'is_multiuse', 'user_id',
                'is_facebook', 'is_40_60', 'is_1free', 'free_delivery', 'is_abandoned_basket', 'is_giftcard', 'is_offline', 'order_id'] as $field) {
                $table->integer($field)->default(0);
            }
            $table->timestamps();
        });
        // Synthetic matrices for the dimensions exercised by legacy creation scenarios.
        DB::table('canvas_header')->where('id', 1)->update(['sizes_30x40' => '30x40[45-15],40x60[65-22],60x80[80-38]']);
        DB::table('gallery_items')->where('id', 1004)->update(['custom_size_prices' => '30x20[18],50x70[35-27]']);
    }
}
