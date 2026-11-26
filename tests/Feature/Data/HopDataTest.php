<?php

use App\Data\HopData;
use App\ValueObjects\HttpStatus;
use App\ValueObjects\RedirectUrl;

it('creates a hop with typed properties', function () {
    $url = new RedirectUrl('https://example.com/step-1');
    $status = new HttpStatus(301);
    $location = '/step-2';

    $hop = new HopData(
        url: $url,
        status: $status,
        location: $location,
    );

    expect($hop->url)->toBe($url)
        ->and($hop->status)->toBe($status)
        ->and($hop->location)->toBe('/step-2');
});

it('creates a hop when location is null', function () {
    $url = new RedirectUrl('https://example.com/final');
    $status = new HttpStatus(200);

    $hop = new HopData(
        url: $url,
        status: $status,
        location: null,
    );

    expect($hop->url)->toBe($url)
        ->and($hop->status)->toBe($status)
        ->and($hop->location)->toBeNull();
});
