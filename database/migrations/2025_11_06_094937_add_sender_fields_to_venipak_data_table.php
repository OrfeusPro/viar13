<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSenderFieldsToVenipakDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('venipak_data', function (Blueprint $table) {
            $table->string('s_name')->nullable()->comment('Название компании отправителя');
            $table->string('s_code')->nullable()->comment('Код предприятия отправителя');
            $table->string('s_country')->nullable()->comment('Страна отправителя');
            $table->string('s_city')->nullable()->comment('Город отправителя');
            $table->string('s_address')->nullable()->comment('Адрес отправителя');
            $table->string('s_post')->nullable()->comment('Почтовый код отправителя');
            $table->string('s_contact_p')->nullable()->comment('Контактное лицо отправителя');
            $table->string('s_contact_t', 30)->nullable()->comment('Телефон отправителя');
            $table->string('email_sender')->nullable()->comment('Email отправителя');
            $table->boolean('is_active')->default(false)->comment('Активный запис');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('venipak_data', function (Blueprint $table) {
            $table->dropColumn([
                's_name',
                's_code',
                's_country',
                's_city',
                's_address',
                's_post',
                's_contact_p',
                's_contact_t',
                'email_sender',
                'is_active',
            ]);
        });
    }
}
