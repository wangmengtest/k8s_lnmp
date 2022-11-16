<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use vhallComponent\decouple\commands\ScheduleCommand;
use vhallComponent\decouple\crontabs\BaseCrontab;
use vhallComponent\decouple\crontabs\ScheduleCrontab;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        ScheduleCrontab::schedule(function (BaseCrontab $cron) use ($schedule) {
            $sch = $schedule->command($cron->name)->cron($cron->cron)->runInBackground();
            if ($cron->singleton) {
                // 防止任务重叠执行
                $sch->withoutOverlapping();
            }

            if ($cron->onOneService) {
                $sch->onOneServer();
            }
        });
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        // 加载组件中的定时任务和命令行
        $this->commands = array_merge(
            $this->commands,
            ScheduleCrontab::load(),
            ScheduleCommand::load()
        );

        require base_path('routes/console.php');
    }
}
