<?php

use App\Data\HopData;
use App\Data\TraceData;
use App\ValueObjects\HttpStatus;
use App\ValueObjects\RedirectUrl;
use Illuminate\Support\Collection;

it('creates a trace with typed properties', function () {
    $firstHop = new HopData(
        url: new RedirectUrl('https://example.com/start'),
        status: new HttpStatus(301),
        location: 'https://example.com/end',
    );
    $secondHop = new HopData(
        url: new RedirectUrl('https://example.com/end'),
        status: new HttpStatus(200),
        location: null,
    );
    $hops = collect([$firstHop, $secondHop]);

    $trace = new TraceData(
        hops: $hops,
        error: 'Connection timed out after 2 hops',
    );

    expect($trace->hops)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(2)
        ->and($trace->hops->first())->toBe($firstHop)
        ->and($trace->hops->last())->toBe($secondHop)
        ->and($trace->error)->toBe('Connection timed out after 2 hops');
});

it('creates a trace when error is null', function () {
    $hops = collect([
        new HopData(
            url: new RedirectUrl('https://example.com/'),
            status: new HttpStatus(200),
            location: null,
        ),
    ]);

    $trace = new TraceData(hops: $hops);

    expect($trace->hops)->toHaveCount(1)
        ->and($trace->error)->toBeNull();
});
