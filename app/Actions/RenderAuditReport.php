<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\AuditData;
use App\Enums\ReportFormat;
use App\Redirects\Reports\ConsoleReport;
use App\Redirects\Reports\JsonReport;
use App\Redirects\Reports\MarkdownReport;

/** Selects and renders an audit report format. */
final class RenderAuditReport
{
    public function handle(AuditData $audit, ReportFormat $format): string
    {
        $report = match ($format) {
            ReportFormat::TABLE => new ConsoleReport,
            ReportFormat::MARKDOWN => new MarkdownReport,
            ReportFormat::JSON => new JsonReport,
        };

        return $report->render($audit);
    }
}
