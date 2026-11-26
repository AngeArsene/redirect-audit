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
     * Create a new RedirectCaseData instance from a CSV row associative array.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $redirectStatus = $data['redirect_status'] ?? null;

        return new self(
            id: (int) $data['id'],
            group: (int) $data['group'],
            source: new RedirectUrl((string) $data['source_url']),
            expected: new RedirectUrl((string) $data['expected_url']),
            range: RedirectRange::between(
                (int) $data['min_hops'],
                (int) $data['max_hops'],
            ),
            redirectStatus: $redirectStatus === null || $redirectStatus === ''
                ? null
                : new HttpStatus((int) $redirectStatus),
            finalStatus: new HttpStatus((int) $data['final_status']),
        );
    }
}
