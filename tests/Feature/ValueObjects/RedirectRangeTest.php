<?php

use App\ValueObjects\RedirectRange;

it('accepts a valid redirect range', function () {
    $min = random_int(0, 100);
    $max = random_int($min, 200);

    $range = new RedirectRange($min, $max);

    expect($range)->toHaveProperty('min', $min)->toHaveProperty('max', $max);
});

dataset('invalid redirect ranges', [
    'negative min' => [-1, 5, 'Minimum hops must be greater than or equal to 0, got [-1].'],
    'negative min and negative max' => [-5, -1, 'Minimum hops must be greater than or equal to 0, got [-5].'],
    'max less than min' => [5, 2, 'Maximum hops must be greater than or equal to minimum [5], got [2].'],
    'max less than min with zero min' => [1, 0, 'Maximum hops must be greater than or equal to minimum [1], got [0].'],
]);

it('rejects an invalid redirect range', function (int $min, int $max, string $message) {
    expect(fn() => new RedirectRange($min, $max))
        ->toThrow(InvalidArgumentException::class, $message);
})->with('invalid redirect ranges');

dataset('redirect range contains cases', [
    'below range' => [1, 2, 0, false],
    'at lower bound' => [1, 2, 1, true],
    'at upper bound' => [1, 2, 2, true],
    'above range' => [1, 2, 3, false],
    'zero range matching zero' => [0, 0, 0, true],
    'zero range above zero' => [0, 0, 1, false],
]);

it('determines whether a redirect count is within the allowed range', function (
    int $min,
    int $max,
    int $count,
    bool $expected
) {
    $range = new RedirectRange($min, $max);

    expect($range->contains($count))->toBe($expected);
})->with('redirect range contains cases');
