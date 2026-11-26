<?php

namespace App\Commands;

use Illuminate\Console\Concerns\CreatesMatchingTest;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Str;

class MakeDtoCommand extends GeneratorCommand
{
    use CreatesMatchingTest;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:dto {name : The name of the data class}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Data class in App\\Data with a Data suffix';

    protected $type = 'Data';

    protected function getStub(): string
    {
        return base_path('stubs/data.stub');
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Data';
    }

    protected function qualifyClass($name): string
    {
        $name = ltrim($name, '\\/');
        $name = str_replace('/', '\\', $name);

        $rootNamespace = $this->rootNamespace();

        if (! Str::startsWith($name, $rootNamespace)) {
            $name = $this->getDefaultNamespace(trim($rootNamespace, '\\')).'\\'.$name;
        }

        $segments = explode('\\', $name);
        $className = array_pop($segments);
        $className = Str::studly($className);

        if (! Str::endsWith($className, 'Data')) {
            $className .= 'Data';
        }

        $segments[] = $className;

        return implode('\\', $segments);
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
