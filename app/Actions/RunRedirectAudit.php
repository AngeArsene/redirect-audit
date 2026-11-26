<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\AuditData;
use App\Data\AuditRequestData;

/** Loads and executes redirect audit cases. */
final class RunRedirectAudit
{
    public function __construct(
        private readonly LoadRedirectCases $loadRedirectCases,
        private readonly AuditRedirectCase $auditRedirectCase,
    ) {}

    public function handle(AuditRequestData $request): AuditData
    {
        $cases = $this->loadRedirectCases->handle($request->csvPath);

        if ($request->groups !== null) {
            $cases = $cases
                ->filter(static fn ($case): bool => in_array($case->group, $request->groups, true))
                ->values();
        }

        $results = collect();
        foreach ($cases as $case) {
            $results->push($this->auditRedirectCase->handle($case));

            if ($request->failFast && $results->last()->verdict->fails()) {
                break;
            }
        }

        return new AuditData($results);
    }
}
