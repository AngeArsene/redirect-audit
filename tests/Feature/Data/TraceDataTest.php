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

it('returns only redirect hops', function () {
    $firstRedirect = new HopData(
        url: new RedirectUrl('http://example.com/'),
        status: new HttpStatus(301),
        location: 'https://example.com/',
    );
    $secondRedirect = new HopData(
        url: new RedirectUrl('https://example.com/'),
        status: new HttpStatus(302),
        location: 'https://example.com/home',
    );
    $finalResponse = new HopData(
        url: new RedirectUrl('https://example.com/home'),
        status: new HttpStatus(200),
        location: null,
    );

    $trace = new TraceData(
        hops: collect([$firstRedirect, $secondRedirect, $finalResponse]),
    );

    $redirects = $trace->redirects();

    expect($redirects)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(2)
        ->and($redirects->all())->toBe([$firstRedirect, $secondRedirect]);
});

it('counts the number of redirects', function () {
    $traceWithRedirects = new TraceData(
        hops: collect([
            new HopData(
                url: new RedirectUrl('http://example.com/'),
                status: new HttpStatus(301),
                location: 'https://example.com/a',
            ),
            new HopData(
                url: new RedirectUrl('https://example.com/a'),
                status: new HttpStatus(307),
                location: 'https://example.com/b',
            ),
            new HopData(
                url: new RedirectUrl('https://example.com/b'),
                status: new HttpStatus(200),
                location: null,
            ),
        ]),
    );

    $traceWithoutRedirects = new TraceData(
        hops: collect([
            new HopData(
                url: new RedirectUrl('https://example.com/'),
                status: new HttpStatus(200),
                location: null,
            ),
        ]),
    );

    expect($traceWithRedirects->redirectCount())->toBe(2)
        ->and($traceWithoutRedirects->redirectCount())->toBe(0)
        ->and($traceWithoutRedirects->redirects())->toBeEmpty();
});
