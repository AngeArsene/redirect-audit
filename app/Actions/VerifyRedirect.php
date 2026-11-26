<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\RedirectCaseData;
use App\Data\TraceData;
use App\Data\VerdictData;
use App\Data\ViolationData;
use App\Enums\ViolationType;
use App\Redirects\Checks\RedirectCheck;
use Illuminate\Support\Collection;

/** Evaluates a completed or failed redirect trace against audit rules. */
final class VerifyRedirect
{
    /** @param Collection<int, RedirectCheck>|array<int, RedirectCheck> $checks */
    public function __construct(private readonly Collection|array $checks) {}

    public function handle(RedirectCaseData $case, TraceData $trace): VerdictData
    {
        if ($trace->failed()) {
            $type = match (true) {
                str_contains(strtolower((string) $trace->error), 'redirect loop') => ViolationType::REDIRECT_LOOP,
                str_contains(strtolower((string) $trace->error), 'safety limit') => ViolationType::REDIRECT_LIMIT_EXCEEDED,
                str_contains(strtolower((string) $trace->error), 'missing location') => ViolationType::MISSING_LOCATION,
                str_contains(strtolower((string) $trace->error), 'invalid redirect') => ViolationType::INVALID_LOCATION,
                default => ViolationType::NETWORK_ERROR,
            };

            $violations = collect([
                new ViolationData(
                    type: $type,
                    message: (string) $trace->error,
                    observed: $trace->error,
                ),
                new ViolationData(
                    type: ViolationType::INCOMPLETE_TRACE,
                    message: 'The redirect trace is incomplete and cannot be considered successful.',
                    observed: $trace->error,
                ),
            ]);

            return new VerdictData($violations);
        }

        $checks = $this->checks instanceof Collection ? $this->checks : collect($this->checks);
        $violations = $checks
            ->flatMap(static fn (RedirectCheck $check): Collection => $check->violations($case, $trace))
            ->values();

        return new VerdictData($violations);
    }
}
