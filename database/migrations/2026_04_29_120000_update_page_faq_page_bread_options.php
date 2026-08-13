<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdatePageFaqPageBreadOptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!DB::getSchemaBuilder()->hasTable('data_types') || !DB::getSchemaBuilder()->hasTable('data_rows')) {
            return;
        }

        $dataType = DB::table('data_types')
            ->where('name', 'page_faq')
            ->orWhere('slug', 'page_faq')
            ->orWhere('slug', 'page-faq')
            ->first();

        if (!$dataType) {
            return;
        }

        $dataRow = DB::table('data_rows')
            ->where('data_type_id', $dataType->id)
            ->where('field', 'page')
            ->first();

        if (!$dataRow) {
            return;
        }

        $details = json_decode($dataRow->details ?? '{}', true);
        if (!is_array($details)) {
            $details = [];
        }

        $details['checked'] = array_key_exists('checked', $details) ? (bool) $details['checked'] : true;

        $options = isset($details['options']) && is_array($details['options']) ? $details['options'] : [];
        $options = array_merge($options, [
            'delivery' => 'Оплата и доставка',
            'review' => 'Отзывы',
            'about' => 'О нас',
            'condition' => 'Условия продажи',
        ]);

        $details['options'] = $options;

        DB::table('data_rows')
            ->where('id', $dataRow->id)
            ->update([
                'details' => json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!DB::getSchemaBuilder()->hasTable('data_types') || !DB::getSchemaBuilder()->hasTable('data_rows')) {
            return;
        }

        $dataType = DB::table('data_types')
            ->where('name', 'page_faq')
            ->orWhere('slug', 'page_faq')
            ->orWhere('slug', 'page-faq')
            ->first();

        if (!$dataType) {
            return;
        }

        $dataRow = DB::table('data_rows')
            ->where('data_type_id', $dataType->id)
            ->where('field', 'page')
            ->first();

        if (!$dataRow) {
            return;
        }

        $details = json_decode($dataRow->details ?? '{}', true);
        if (!is_array($details)) {
            return;
        }

        if (isset($details['options']) && is_array($details['options'])) {
            unset(
                $details['options']['delivery'],
                $details['options']['review'],
                $details['options']['about'],
                $details['options']['condition']
            );
        }

        DB::table('data_rows')
            ->where('id', $dataRow->id)
            ->update([
                'details' => json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
    }
}
