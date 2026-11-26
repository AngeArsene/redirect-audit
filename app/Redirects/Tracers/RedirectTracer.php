<?php

declare(strict_types=1);

namespace App\Redirects\Tracers;

use App\Data\TraceData;
use App\ValueObjects\RedirectUrl;

interface RedirectTracer
{
    public function trace(RedirectUrl $url): TraceData;
}
