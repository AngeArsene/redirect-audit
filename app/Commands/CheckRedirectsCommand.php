<?php

declare(strict_types=1);

namespace App\Commands;

use App\Actions\RenderAuditReport;
use App\Actions\RunRedirectAudit;
use App\Data\AuditRequestData;
use App\Enums\ReportFormat;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

/** Coordinates redirect audit input, execution, and reporting. */
final class CheckRedirectsCommand extends Command
{
    protected $signature = 'redirects:check'
        .' {--cases= : Path to the CSV file containing audit cases}'
        .' {--test= : Filter by comma-separated test group numbers}'
        .' {--format=table : Report format: table, md, or json}'
        .' {--output= : Write the report to an external file}'
        .' {--fail-fast : Stop after the first failed case}';

    protected $description = 'Audit HTTP redirect chains against expected URLs and status codes defined in a CSV file.';

    public function handle(
        RunRedirectAudit $runRedirectAudit,
        RenderAuditReport $renderAuditReport,
    ): int {
        $csv = $this->option('cases');
        if (! is_string($csv) || trim($csv) === '') {
            $this->error('The --cases option is required.');

            return 2;
        }

        $csv = $this->resolvePath($csv);

        try {
            $format = ReportFormat::fromCli((string) $this->option('format'));
        } catch (\InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return 2;
        }

        $groups = null;
        $test = $this->option('test');
        if (is_string($test) && trim($test) !== '') {
            try {
                $groups = collect(explode(',', $test))
                    ->map(static fn (string $group): string => trim($group))
                    ->filter(static fn (string $group): bool => $group !== '')
                    ->map(function (string $group): int {
                        if (! preg_match('/^\d+$/', $group)) {
                            throw new \InvalidArgumentException("Invalid test group [{$group}].");
                        }

                        return (int) $group;
                    })
                    ->unique()
                    ->values()
                    ->all();
            } catch (\InvalidArgumentException $exception) {
                $this->error($exception->getMessage());

                return 2;
            }

            if ($groups === []) {
                $this->error('The --test option must contain at least one valid group number.');

                return 2;
            }
        }

        try {
            $audit = $runRedirectAudit->handle(new AuditRequestData(
                csvPath: $csv,
                groups: $groups,
                failFast: (bool) $this->option('fail-fast'),
            ));
        } catch (\InvalidArgumentException|\RuntimeException $exception) {
            $this->error($exception->getMessage());

            return 2;
        } catch (\Throwable $exception) {
            $this->error('Unable to execute audit: '.$exception->getMessage());

            return 2;
        }

        $report = $renderAuditReport->handle($audit, $format);

        $output = $this->option('output');
        if (is_string($output) && trim($output) !== '') {
            $output = $this->resolvePath($output);
            if (is_file($output)) {
                $this->error("Report file already exists [{$output}].");

                return 2;
            }

            $directory = dirname($output);
            if (! is_dir($directory) && ! mkdir($directory, 0777, true) && ! is_dir($directory)) {
                $this->error("Unable to create report directory [{$directory}].");

                return 2;
            }

            if (file_put_contents($output, $report) === false) {
                $this->error("Unable to write report [{$output}].");

                return 2;
            }

            $this->line("Report written to {$output}");
        } else {
            $this->line($report);
        }

        return $audit->passes() ? 0 : 1;
    }

    private function resolvePath(string $path): string
    {
        if (preg_match('/^(?:[A-Za-z]:[\\\\\/]|[\\\\\/]|https?:)/', $path) === 1) {
            return $path;
        }

        return base_path($path);
    }

    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
