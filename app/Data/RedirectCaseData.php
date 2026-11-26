<?php

declare(strict_types=1);

namespace App\Data;

use App\ValueObjects\HttpStatus;
use App\ValueObjects\RedirectRange;
use App\ValueObjects\RedirectUrl;

/**
 * Immutable data transfer object for RedirectCaseData.
 */
final readonly class RedirectCaseData
{
    /**
     * Create a new RedirectCaseData instance.
     */
    public function __construct(
        public int $id,
        public int $group,
        public RedirectUrl $source,
        public RedirectUrl $expected,
        public RedirectRange $range,
        public ?HttpStatus $redirectStatus,
        public HttpStatus $finalStatus,
    ) {}

    /**
     * Convert the RedirectCaseData instance into a plain array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'group' => $this->group,
            'source' => $this->source->value,
            'expected' => $this->expected->value,
            'range' => [
                'min' => $this->range->min,
                'max' => $this->range->max,
            ],
            'redirectStatus' => $this->redirectStatus?->value,
            'finalStatus' => $this->finalStatus->value,
        ];
    }

    /**
     * Create a new RedirectCaseData instance from an array of attributes.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function fromArray(array $attributes): self
    {
        return new self();
    }
}
