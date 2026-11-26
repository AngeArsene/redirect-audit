<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Support\Collection;

/** Represents aggregate results for an executed audit. */
final readonly class AuditData
{
    /** @param Collection<int, CaseResultData> $results */
    public function __construct(public Collection $results) {}

    public function totalCount(): int
    {
        return $this->results->count();
    }

    public function passedCount(): int
    {
        return $this->passedResults()->count();
    }

    public function failedCount(): int
    {
        return $this->failedResults()->count();
    }

    /** @return Collection<int, CaseResultData> */
    public function passedResults(): Collection
    {
        return $this->results
            ->filter(static fn (CaseResultData $result): bool => $result->verdict->passes())
            ->values();
    }

    /** @return Collection<int, CaseResultData> */
    public function failedResults(): Collection
    {
        return $this->results
            ->filter(static fn (CaseResultData $result): bool => $result->verdict->fails())
            ->values();
    }

    public function passes(): bool
    {
        return $this->totalCount() > 0 && $this->failedCount() === 0;
    }

    /** @return Collection<int, CaseResultData> */
    public function resultsByGroup(int $group): Collection
    {
        return $this->results
            ->filter(static fn (CaseResultData $result): bool => $result->case->group === $group)
            ->values();
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'summary' => [
                'total' => $this->totalCount(),
                'passed' => $this->passedCount(),
                'failed' => $this->failedCount(),
                'unexecuted' => 0,
                'completed' => $this->passes(),
            ],
            'results' => $this->results
                ->map(static fn (CaseResultData $result): array => $result->toArray())
                ->all(),
        ];
    }
}
