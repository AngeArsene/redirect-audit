<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ViolationType;

/** Represents a structured redirect audit violation. */
final readonly class ViolationData
{
    public function __construct(
        public ViolationType $type,
        public string $message,
        public mixed $expected = null,
        public mixed $observed = null,
        public ?HopData $hop = null,
    ) {}

    /** Convert the violation into a plain array. */
    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'message' => $this->message,
            'expected' => $this->expected,
            'observed' => $this->observed,
            'hop' => $this->hop?->toArray(),
        ];
    }
}
