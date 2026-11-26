<?php

namespace App\ValueObjects;

use InvalidArgumentException;

/**
 * Class RedirectUrl
 */
final readonly class RedirectUrl
{
    /**
     * Create a new value object instance.
     */
    public function __construct(public string $value)
    {
        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException("Invalid redirect URL [{$value}].");
        }

        $scheme = parse_url($value, PHP_URL_SCHEME);

        if (! is_string($scheme) || ! in_array(strtolower($scheme), ['http', 'https'], true)) {
            throw new InvalidArgumentException("Redirect URL scheme must be http or https, got [{$value}].");
        }
    }

    /**
     * Determine equality with another instance.
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
