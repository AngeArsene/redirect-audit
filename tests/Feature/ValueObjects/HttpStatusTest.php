<?php

use App\ValueObjects\HttpStatus;

it('rejects status codes outside 100 to 599', function () {
    $count = random_int(1, 10);
    $invalidValues = array_map(
        static fn (): int => random_int(0, 1) === 0
            ? random_int(PHP_INT_MIN, 99)
            : random_int(600, PHP_INT_MAX),
        range(1, $count),
    );

    foreach ($invalidValues as $value) {
        expect(fn () => new HttpStatus($value))
            ->toThrow(
                InvalidArgumentException::class,
                "HTTP status code must be between 100 and 599, got [{$value}].",
            );
    }
});

it('accepts a valid HTTP status code', function () {
    $count = random_int(1, 10); // random number of items (e.g. 1 to 10)

    $validValues = array_map(
        static fn(): int => random_int(100, 599),
        range(1, $count),
    );

    foreach ($validValues as $value) {
        expect(new HttpStatus($value))
            ->toHaveProperty('value', $value);
    }
});

dataset('http redirect statuses', [
    '200 OK' => [200, false],
    '301 Moved Permanently' => [301, true],
    '302 Found' => [302, true],
    '303 See Other' => [303, true],
    '304 Not Modified' => [304, false],
    '307 Temporary Redirect' => [307, true],
    '308 Permanent Redirect' => [308, true],
    '404 Not Found' => [404, false],
    '500 Internal Server Error' => [500, false],
]);

it('determines whether an HTTP status code is a followable redirect', function (int $status, bool $expected) {
    $status = new HttpStatus($status);

    expect($status->isRedirect())->toBe($expected);
})->with('http redirect statuses');

dataset('http status equality cases', [
    'same status code' => [200, 200, true],
    'different status codes' => [200, 301, false],
    'redirect vs non-redirect' => [302, 404, false],
    'both redirects with different codes' => [301, 308, false],
]);

it('checks equality between two HttpStatusCode instances', function (int $first, int $second, bool $expected) {
    $a = new HttpStatus($first);
    $b = new HttpStatus($second);

    expect($a->equals($b))->toBe($expected);
})->with('http status equality cases');

it('is reflexive when comparing an instance to itself', function () {
    $status = new HttpStatus(200);

    expect($status->equals($status))->toBeTrue();
});
