<?php

namespace App\Console;

use DateTimeZone;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\{Soundblock\Ledger\UpdateLedgerJob};
use App\Console\Commands\{Apparel\ReplaceProductId,
    Apparel\ScrapingDataRelease,
    LedgerConsole,
    Migration\FreshCommand,
    Soundblock\ServiceTransactions,
    User\RemoveNotVerifiedEmails
};

class Kernel extends ConsoleKernel {
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        ServiceTransactions::class,
        FreshCommand::class,
        LedgerConsole::class,
//        GetDataConsole::class,
        RemoveNotVerifiedEmails::class,
        ReplaceProductId::class,
        ScrapingDataRelease::class,
    ];

    /**
     * Define the application"s command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule) {
        $schedule->command("caching")->dailyAt("12:00")->timezone("America/New_York")
                 ->before(function () {
                     echo "Starting cache job." . PHP_EOL;
                 })
                 ->after(function () {
                     echo "Ending cache job." . PHP_EOL;
                 })->appendOutputTo("storage/schedule.log");

        /*
         * TASK FOR SERVICE CHARGES
         * */
        $schedule->command("charge")->dailyAt("08:00")->timezone("America/New_York")->before(function () {
            echo "Starting charge job.\n";
        })->after(function () {
            echo "Ending charge job.\n";
        })->appendOutputTo("storage/schedule.log");

//        $schedule->job(new UpdateLedgerJob());

        /*
         * TASK FOR REMOVING NOT VERIFIED EMAILS
         * */

        $schedule->command("user:emails:clear")->dailyAt("00:00")->timezone("America/New_York")->before(function () {
            echo "Starting clearing not verified emails job.\n";
        })->after(function () {
            echo "Ending clearing not verified emails job.\n";
        })->appendOutputTo("storage/schedule.log");
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands() {
        $this->load(__DIR__ . "/Commands");

        require base_path("routes/console.php");
    }

    /**
     * Get the timezone that should be used by default for scheduled
     * @return DateTimeZone|string|null
     */

    protected function scheduleTimezone() {
        return ("America/New_York");
    }
}
