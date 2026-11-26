<?php

declare(strict_types=1);

namespace App\Redirects\Checks;

use App\Data\RedirectCaseData;
use App\Data\TraceData;
use App\Data\ViolationData;
use App\Enums\ViolationType;
use Illuminate\Support\Collection;

final class RedirectStatusCheck implements RedirectCheck
{
    public function violations(RedirectCaseData $case, TraceData $trace): Collection
    {
        if ($case->redirectStatus === null) {
            return collect();
        }

        return $trace->redirects()
            ->reject(fn ($hop): bool => $hop->status->equals($case->redirectStatus))
            ->map(fn ($hop): ViolationData => new ViolationData(
                type: ViolationType::WRONG_REDIRECT_STATUS,
                message: "Expected redirect status {$case->redirectStatus->value}, observed {$hop->status->value}.",
                expected: $case->redirectStatus->value,
                observed: $hop->status->value,
                hop: $hop,
            ))
            ->values();
    }
}
