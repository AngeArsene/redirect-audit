<?php

declare(strict_types=1);

namespace App\Redirects\Reports;

use App\Data\AuditData;
use App\Data\CaseResultData;

/** Renders a redirect audit as Markdown. */
final class MarkdownReport implements AuditReport
{
    public function render(AuditData $audit): string
    {
        $lines = [
            '# Redirect Audit Report',
            '',
            '| Status | Case | Group | Source | Observed destination |',
            '|---|---:|---:|---|---|',
        ];

        $audit->results->each(function (CaseResultData $result) use (&$lines): void {
            $status = $result->verdict->passes() ? 'PASS' : 'FAIL';
            $observed = $result->trace->finalHop()?->url->value ?? 'unavailable';
            $lines[] = "| {$status} | {$result->case->id} | {$result->case->group} | {$result->case->source->value} | {$observed} |";

            if ($result->verdict->fails()) {
                $result->verdict->violations->each(
                    static fn ($violation) => $lines[] = "- **{$violation->type->value}**: {$violation->message}",
                );
            }
        });

        $lines[] = '';
        $lines[] = '## Summary';
        $lines[] = '';
        $lines[] = "- Total: {$audit->totalCount()}";
        $lines[] = "- Passed: {$audit->passedCount()}";
        $lines[] = "- Failed: {$audit->failedCount()}";
        $lines[] = '- Status: '.($audit->passes() ? 'Complete' : 'Incomplete');

        return implode("\n", $lines);
    }
}
