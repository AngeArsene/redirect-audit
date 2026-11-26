<?php

namespace App\Commands;

use App\Actions\LoadRedirectCases;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class CheckRedirectsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redirects:check {--cases= : Path to the CSV file containing audit cases}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit HTTP redirect chains against expected URLs and status codes defined in a CSV file.';

    /**
     * Execute the console command.
     */
    public function handle(LoadRedirectCases $loadRedirectCases)
    {
        $cases = $loadRedirectCases->handle($this->option('cases'));

        dd($cases);
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
