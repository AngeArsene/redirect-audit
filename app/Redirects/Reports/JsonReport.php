<?php

declare(strict_types=1);

namespace App\Redirects\Reports;

use App\Data\AuditData;

/** Renders a machine-readable redirect audit as JSON. */
final class JsonReport implements AuditReport
{
    public function render(AuditData $audit): string
    {
        $payload = [
            'executedAt' => (new \DateTimeImmutable('now'))->format(DATE_ATOM),
            'summary' => [
                'total' => $audit->totalCount(),
                'passed' => $audit->passedCount(),
                'failed' => $audit->failedCount(),
                'unexecuted' => 0,
                'completed' => $audit->passes(),
            ],
            'results' => $audit->results
                ->map(static fn ($result): array => $result->toArray())
                ->all(),
        ];

        return json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }
}
