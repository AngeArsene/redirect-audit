<?php

declare(strict_types=1);

namespace Tests\Fakes;

use App\Data\TraceData;
use App\Redirects\Tracers\RedirectTracer;
use App\ValueObjects\RedirectUrl;

/** Supplies deterministic traces for audit orchestration tests. */
final class FakeRedirectTracer implements RedirectTracer
{
    public function __construct(private readonly TraceData $trace) {}

    public function trace(RedirectUrl $url): TraceData
    {
        return $this->trace;
    }
}
