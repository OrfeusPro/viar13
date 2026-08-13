<?php

namespace App\Console\Commands;

use App\Services\OrderOverdueDelayNotifier;
use Illuminate\Console\Command;

class OrderOverdueDelayNotifyCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:notify-overdue-delay';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify customers about delayed order shipping';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $result = app(OrderOverdueDelayNotifier::class)->sendDueNotifications();

        $this->info('Order overdue delay notifications: ' . json_encode($result));
    }
}
