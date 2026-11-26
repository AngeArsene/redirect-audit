<?php

namespace App\ValueObjects;

/**
 * Class RedirectRange
 */
final readonly class RedirectRange
{
    /**
     * Create a new value object instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine equality with another instance.
     */
    public function equals(self $other): bool
    {
        return $this == $other;
    }
}
