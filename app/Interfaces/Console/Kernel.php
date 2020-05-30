<?php

namespace Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $month = date('m') . '/' . date('Y');

            \Model\Shop::chunk(4, function ($shops) use ($month){
                foreach($shops as $shop){
                    \Reports\Service::generateReportByGivenMonthAndYearAndSave($shop->id, $month);
                }
            });
        })->dailyAt('23:59')->when(function () {
            return \Carbon\Carbon::now()->endOfMonth()->isToday();
        });
    }
}
