<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRegistrationFieldsToUsers extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('registration_page')->nullable()->after('remember_token');
            $table->string('referrer_url')->nullable()->after('registration_page');
            $table->json('utm_parameters')->nullable()->after('referrer_url');
            $table->string('user_agent')->nullable()->after('utm_parameters');
            $table->ipAddress('last_ip')->nullable()->after('user_agent');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['registration_page', 'referrer_url', 'utm_parameters' , 'user_agent' ]);
        });
    }
}