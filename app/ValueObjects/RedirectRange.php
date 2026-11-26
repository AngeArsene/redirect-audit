<?php

namespace App\ValueObjects;

use InvalidArgumentException;

/**
 * Represents a validated inclusive range of redirect hops.
 */
final readonly class RedirectRange
{
    /**
     * Creates a redirect range with valid minimum and maximum hops.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(public int $min, public int $max)
    {
        if ($this->min < 0) {
            throw new InvalidArgumentException("Minimum hops must be greater than or equal to 0, got [{$this->min}].");
        }

        if ($this->max < $this->min) {
            throw new InvalidArgumentException("Maximum hops must be greater than or equal to minimum [{$this->min}], got [{$this->max}].");
        }
    }

    /**
     * Determines whether a hop count falls within this range.
     */
    public function contains(int $count): bool
    {
        return ($count >= $this->min && $count <= $this->max);
    }

    /**
     * Determine equality with another instance.
     */
    public function equals(self $other): bool
    {
        return $this->min === $other->min && $this->max === $other->max;
    }

    /**
     * Creates a range containing exactly one hop count.
     */
    public static function exactly(int $count): self
    {
        return new self($count, $count);
    }

    /**
     * Creates an inclusive range between two hop counts.
     */
    public static function between(int $min, int $max): self
    {
        return new self($min, $max);
    }

    /**
     * Creates a range that contains no hops.
     */
    public static function none(): self
    {
        return new self(0, 0);
    }
}
