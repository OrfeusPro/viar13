<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**

     * The Artisan commands provided by your application.

     *

     * @var array

     */
    protected $commands = [

        \App\Console\Commands\UserNotifyCron::class,
        \App\Console\Commands\OrderOverdueDelayNotifyCron::class,
        \App\Console\Commands\AltScanCommand::class,
        \App\Console\Commands\AltGenerateCommand::class,
        \App\Console\Commands\AltApplyCommand::class,
        \App\Console\Commands\AltBackfillUrlsCommand::class,
        \App\Console\Commands\SeoMetaGenerateCommand::class,
        \App\Console\Commands\SeoMetaScanCommand::class,
        \App\Console\Commands\GenerateWebpImagesCommand::class,

    ];

    /**

     * Define the application's command schedule.

     *

     * @param \Illuminate\Console\Scheduling\Schedule $schedule

     *

     * @return void

     */
    protected function schedule(Schedule $schedule)
    {

        // $schedule->command('UserNotify:cron')->daily();

        $schedule->command('UserNotify:cron')->daily()->withoutOverlapping();
        $schedule->command('orders:notify-overdue-delay')->dailyAt('18:00')->withoutOverlapping();
        $schedule->command('cart:send-recovery-emails')->hourly()->withoutOverlapping();
        $schedule->command('cart:send-recovery-emails-coupons')->hourly()->withoutOverlapping();
        $schedule->command('GenSmallImages:run')->daily()->withoutOverlapping();
    }

    /**

     * Register the commands for the application.

     *

     * @return void

     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
