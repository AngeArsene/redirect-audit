<?php

declare(strict_types=1);

namespace App\Redirects\Checks;

use App\Data\RedirectCaseData;
use App\Data\TraceData;
use App\Data\ViolationData;
use Illuminate\Support\Collection;

interface RedirectCheck
{
    /** @return Collection<int, ViolationData> */
    public function violations(RedirectCaseData $case, TraceData $trace): Collection;
}
