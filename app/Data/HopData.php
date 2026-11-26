<?php

declare(strict_types=1);

namespace App\Data;

use App\ValueObjects\HttpStatus;
use App\ValueObjects\RedirectUrl;

/**
 * Immutable data transfer object for HopData.
 */
final readonly class HopData
{
    /**
     * Create a new HopData instance.
     */
    public function __construct(
        public RedirectUrl $url,
        public HttpStatus $status,
        public ?string $location = null,
    ) {}

    /**
     * Create a new HopData instance from an array of attributes.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function fromArray(array $attributes): self
    {
        $location = $attributes['location'] ?? null;

        return new self(
            url: $attributes['url'] instanceof RedirectUrl
                ? $attributes['url']
                : new RedirectUrl((string) $attributes['url']),
            status: $attributes['status'] instanceof HttpStatus
                ? $attributes['status']
                : new HttpStatus((int) $attributes['status']),
            location: $location === null || $location === ''
                ? null
                : (string) $location,
        );
    }

    /**
     * Convert the HopData instance into a plain array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'url' => $this->url->value,
            'status' => $this->status->value,
            'location' => $this->location,
        ];
    }
}
