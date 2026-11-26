<?php

declare(strict_types=1);

namespace App\Redirects\Checks;

use App\Data\RedirectCaseData;
use App\Data\TraceData;
use App\Data\ViolationData;
use App\Enums\ViolationType;
use Illuminate\Support\Collection;

final class FinalStatusCheck implements RedirectCheck
{
    public function violations(RedirectCaseData $case, TraceData $trace): Collection
    {
        $finalHop = $trace->finalHop();
        if ($trace->failed() || $finalHop === null) {
            return collect();
        }

        if ($finalHop->status->equals($case->finalStatus)) {
            return collect();
        }

        return collect([
            new ViolationData(
                type: ViolationType::WRONG_FINAL_STATUS,
                message: "Expected final status {$case->finalStatus->value}, observed {$finalHop->status->value}.",
                expected: $case->finalStatus->value,
                observed: $finalHop->status->value,
                hop: $finalHop,
            ),
        ]);
    }
}
