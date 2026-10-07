<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGuestCouponRecoveryIdentity extends Migration
{
    public function up()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->string('recipient_email')->nullable()->index();
            $table->string('recovery_event_key', 64)->nullable()->unique();
        });
    }

    public function down()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropUnique(['recovery_event_key']);
            $table->dropIndex(['recipient_email']);
            $table->dropColumn(['recipient_email', 'recovery_event_key']);
        });
    }
}
