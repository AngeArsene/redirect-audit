<?php

declare(strict_types=1);

namespace App\Redirects\Reports;

use App\Data\AuditData;
use App\Data\CaseResultData;

/** Renders a redirect audit for terminal output. */
final class ConsoleReport implements AuditReport
{
    public function render(AuditData $audit): string
    {
        $lines = $audit->results
            ->map(function (CaseResultData $result): string {
                $status = $result->verdict->passes() ? 'PASS' : 'FAIL';
                $observed = $result->trace->finalHop()?->url->value ?? 'unavailable';
                $line = "{$status} | case {$result->case->id} | {$result->case->source->value} | observed {$observed}";

                if ($result->verdict->fails()) {
                    $violations = $result->verdict->violations
                        ->map(static fn ($violation): string => "- {$violation->message}")
                        ->implode("\n");
                    $line .= "\n{$violations}";
                }

                return $line;
            })
            ->all();

        $lines[] = "Summary: {$audit->totalCount()} total, {$audit->passedCount()} passed, {$audit->failedCount()} failed.";

        return implode("\n", $lines);
    }
}
