<?php

namespace App\ValueObjects;

use InvalidArgumentException;

/**
 * Represents a validated HTTP status code.
 */
final readonly class HttpStatus
{
    /**
     * @var list<int>
     */
    private const REDIRECTION_STATUS_CODES = [301, 302, 303, 307, 308];

    /**
     * Creates an HTTP status value within the valid range.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(public int $value)
    {
        if (! ($value >= 100 && $value <= 599)) {
            throw new InvalidArgumentException("HTTP status code must be between 100 and 599, got [{$value}].");
        }
    }

    /**
     * Determines whether this status code represents a redirect.
     */
    public function isRedirect(): bool
    {
        return in_array($this->value, self::REDIRECTION_STATUS_CODES, true);
    }

    /**
     * Determine equality with another instance.
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
