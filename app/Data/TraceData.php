<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Support\Collection;

/**
 * Immutable data transfer object for TraceData.
 */
final readonly class TraceData
{
    /**
     * Create a new TraceData instance.
     *
     * @param  Collection<int, HopData>  $hops
     */
    public function __construct(
        public Collection $hops,
        public ?string $error = null,
    ) {}

    /**
     * Create a new TraceData instance from an array of attributes.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function fromArray(array $attributes): self
    {
        $rawHops = $attributes['hops'] ?? [];

        $hops = $rawHops instanceof Collection
            ? $rawHops
            : collect(is_iterable($rawHops) ? $rawHops : []);

        $normalizedHops = $hops->map(
            static fn (mixed $hop): HopData => $hop instanceof HopData
                ? $hop
                : HopData::fromArray((array) $hop),
        )->values();

        $error = $attributes['error'] ?? null;

        return new self(
            hops: $normalizedHops,
            error: $error === null || $error === '' ? null : (string) $error,
        );
    }

    /**
     * Convert the TraceData instance into a plain array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'hops' => $this->hops
                ->map(static fn (HopData $hop): array => $hop->toArray())
                ->all(),
            'error' => $this->error,
        ];
    }
}
