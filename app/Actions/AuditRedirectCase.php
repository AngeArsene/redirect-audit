<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\CaseResultData;
use App\Data\RedirectCaseData;
use App\Redirects\Tracers\RedirectTracer;

/** Traces and verifies one redirect audit case. */
final class AuditRedirectCase
{
    public function __construct(
        private readonly RedirectTracer $tracer,
        private readonly VerifyRedirect $verifyRedirect,
    ) {}

    public function handle(RedirectCaseData $case): CaseResultData
    {
        $trace = $this->tracer->trace($case->source);
        $verdict = $this->verifyRedirect->handle($case, $trace);

        return new CaseResultData($case, $trace, $verdict);
    }
}
