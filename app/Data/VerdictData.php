<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Support\Collection;

/** Represents the verification result for one audit case. */
final readonly class VerdictData
{
    /** @param Collection<int, ViolationData> $violations */
    public function __construct(public Collection $violations) {}

    public function passes(): bool
    {
        return $this->violations->isEmpty();
    }

    public function fails(): bool
    {
        return ! $this->passes();
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'passed' => $this->passes(),
            'violations' => $this->violations
                ->map(static fn (ViolationData $violation): array => $violation->toArray())
                ->all(),
        ];
    }
}
