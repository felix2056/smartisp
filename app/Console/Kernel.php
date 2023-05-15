<?php

namespace App\Console;

use App\Console\Commands\ActivateDeactivatedClients;
use App\Console\Commands\AddMIssingToMIkrotik;
use App\Console\Commands\ChangeStatusOnNegativeBalance;
use App\Console\Commands\ClientAddressListCheck;
use App\Console\Commands\GenerateCsvPaidInvoices;
use App\Console\Commands\GenerateInvoice;
use App\Console\Commands\GenerateRecurringInvoice;
use App\Console\Commands\GetDataSmartOLT;
use App\Console\Commands\PaymentReminderCommand;
use App\Console\Commands\QueueCheckup;
use App\Console\Commands\RemoveBackup;
use App\Console\Commands\SetActiveBlockedClients;
use App\Console\Commands\SetMikrotikRulesForBlockedClients;
use App\Console\Commands\UpdateRouterStatus;
use App\libraries\Helpers;
use App\models\GlobalSetting;
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
        GenerateInvoice::class,
        ChangeStatusOnNegativeBalance::class,
        SetMikrotikRulesForBlockedClients::class,
        UpdateRouterStatus::class,
        SetActiveBlockedClients::class,
        RemoveBackup::class,
        ClientAddressListCheck::class,
        QueueCheckup::class,
        GenerateRecurringInvoice::class,
        AddMIssingToMIkrotik::class,
        ActivateDeactivatedClients::class,
        PaymentReminderCommand::class,
        GetDataSmartOLT::class,
//        GenerateCsvPaidInvoices::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $interval = GlobalSetting::first();
	    $schedule->command(ChangeStatusOnNegativeBalance::class)->withoutOverlapping()->runInBackground()->daily()->at('00:00');
	    $schedule->command(GenerateInvoice::class)->daily()->runInBackground()->withoutOverlapping()->at('02:00');
	    $schedule->command(RemoveBackup::class)->weekends()->runInBackground()->withoutOverlapping()->at('22:00');
	    $schedule->command(GenerateRecurringInvoice::class)->runInBackground()->withoutOverlapping()->daily()->at('03:00');
//	    $schedule->command(GenerateCsvPaidInvoices::class)->runInBackground()->withoutOverlapping()->daily()->at('04:00');
        $schedule->command(SetMikrotikRulesForBlockedClients::class)->runInBackground()->cron('0 */2 * * *');
        if($interval) {
            $schedule->command(UpdateRouterStatus::class)->cron('0 */'.$interval->router_interval.' * * *')->runInBackground()->withoutOverlapping();
        }
        $schedule->command(ClientAddressListCheck::class)->cron('0 */2 * * *')->runInBackground()->withoutOverlapping();
        $schedule->command(PaymentReminderCommand::class)->cron('*/5 * * * *')->runInBackground()->withoutOverlapping();

        $smartolt =  Helpers::get_api_options('smartolt');
        if(isset($smartolt['c']))
            $schedule->command(GetDataSmartOLT::class)->cron('*/20 * * * *');
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
