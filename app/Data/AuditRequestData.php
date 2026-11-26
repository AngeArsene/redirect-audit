<?php

declare(strict_types=1);

namespace App\Data;

/** Represents validated input required to run an audit. */
final readonly class AuditRequestData
{
    /** @param list<int>|null $groups */
    public function __construct(
        public string $csvPath,
        public ?array $groups = null,
        public bool $failFast = false,
    ) {}
}
