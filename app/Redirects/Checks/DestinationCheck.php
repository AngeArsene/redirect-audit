<?php

declare(strict_types=1);

namespace App\Redirects\Checks;

use App\Data\RedirectCaseData;
use App\Data\TraceData;
use App\Data\ViolationData;
use App\Enums\ViolationType;
use Illuminate\Support\Collection;

final class DestinationCheck implements RedirectCheck
{
    public function violations(RedirectCaseData $case, TraceData $trace): Collection
    {
        $finalHop = $trace->finalHop();
        if ($trace->failed() || $finalHop === null) {
            return collect();
        }

        if ($finalHop->url->equals($case->expected)) {
            return collect();
        }

        return collect([
            new ViolationData(
                type: ViolationType::WRONG_DESTINATION,
                message: "Expected destination {$case->expected->value}, observed {$finalHop->url->value}.",
                expected: $case->expected->value,
                observed: $finalHop->url->value,
                hop: $finalHop,
            ),
        ]);
    }
}
