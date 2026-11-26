<?php

declare(strict_types=1);

namespace App\Data;

/** Connects an audit case to its trace and verdict. */
final readonly class CaseResultData
{
    public function __construct(
        public RedirectCaseData $case,
        public TraceData $trace,
        public VerdictData $verdict,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'case' => $this->case->toArray(),
            'trace' => $this->trace->toArray(),
            'verdict' => $this->verdict->toArray(),
        ];
    }
}
