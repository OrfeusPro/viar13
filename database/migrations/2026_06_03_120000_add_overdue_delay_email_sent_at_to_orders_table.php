<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOverdueDelayEmailSentAtToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('overdue_delay_email_sent_at')->nullable()->after('send_date');
            $table->index('overdue_delay_email_sent_at', 'orders_overdue_delay_email_sent_at_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_overdue_delay_email_sent_at_index');
            $table->dropColumn('overdue_delay_email_sent_at');
        });
    }
}
