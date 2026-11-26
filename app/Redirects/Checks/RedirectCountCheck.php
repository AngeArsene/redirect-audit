<?php

declare(strict_types=1);

namespace App\Redirects\Checks;

use App\Data\RedirectCaseData;
use App\Data\TraceData;
use App\Data\ViolationData;
use App\Enums\ViolationType;
use Illuminate\Support\Collection;

final class RedirectCountCheck implements RedirectCheck
{
    public function violations(RedirectCaseData $case, TraceData $trace): Collection
    {
        $observed = $trace->redirectCount();

        if ($case->range->contains($observed)) {
            return collect();
        }

        return collect([
            new ViolationData(
                type: ViolationType::REDIRECT_COUNT_MISMATCH,
                message: "Expected {$case->range->min} to {$case->range->max} redirects, observed {$observed}.",
                expected: ['min' => $case->range->min, 'max' => $case->range->max],
                observed: $observed,
            ),
        ]);
    }
}
