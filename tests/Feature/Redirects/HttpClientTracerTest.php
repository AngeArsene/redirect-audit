<?php

declare(strict_types=1);

use App\Redirects\Tracers\HttpClientTracer;
use App\ValueObjects\RedirectUrl;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

it('traces a direct HTTP response', function () {
    Http::fake([
        'https://example.com/' => Http::response([], 200),
    ]);

    $trace = (new HttpClientTracer)->trace(new RedirectUrl('https://example.com/'));

    expect($trace->failed())->toBeFalse()
        ->and($trace->redirectCount())->toBe(0)
        ->and($trace->finalHop()?->status->value)->toBe(200);

    Http::assertSentCount(1);
});

it('traces a single redirect and resolves a relative Location header', function () {
    Http::fake([
        'https://example.com/start' => Http::response([], 301, ['Location' => '../final']),
        'https://example.com/final' => Http::response([], 200),
    ]);

    $trace = (new HttpClientTracer)->trace(new RedirectUrl('https://example.com/start'));

    expect($trace->failed())->toBeFalse()
        ->and($trace->redirectCount())->toBe(1)
        ->and($trace->finalHop()?->url->value)->toBe('https://example.com/final');
});

it('traces multiple redirects', function () {
    Http::fake([
        'https://example.com/start' => Http::response([], 301, ['Location' => '/middle']),
        'https://example.com/middle' => Http::response([], 308, ['Location' => 'final']),
        'https://example.com/final' => Http::response([], 404),
    ]);

    $trace = (new HttpClientTracer)->trace(new RedirectUrl('https://example.com/start'));

    expect($trace->redirectCount())->toBe(2)
        ->and($trace->finalHop()?->status->value)->toBe(404);
});

it('records missing Location headers as a trace failure', function () {
    Http::fake([
        'https://example.com/' => Http::response([], 302),
    ]);

    $trace = (new HttpClientTracer)->trace(new RedirectUrl('https://example.com/'));

    expect($trace->failed())->toBeTrue()
        ->and($trace->error)->toContain('Missing Location');
});

it('rejects unsupported redirect protocols', function () {
    Http::fake([
        'https://example.com/' => Http::response([], 301, ['Location' => 'ftp://example.com/file']),
    ]);

    $trace = (new HttpClientTracer)->trace(new RedirectUrl('https://example.com/'));

    expect($trace->failed())->toBeTrue()
        ->and($trace->error)->toContain('Invalid redirect location');
});

it('detects redirect loops', function () {
    Http::fake([
        'https://example.com/a' => Http::response([], 301, ['Location' => '/b']),
        'https://example.com/b' => Http::response([], 301, ['Location' => '/a']),
    ]);

    $trace = (new HttpClientTracer)->trace(new RedirectUrl('https://example.com/a'));

    expect($trace->failed())->toBeTrue()
        ->and($trace->error)->toContain('Redirect loop');
});

it('enforces the redirect safety limit', function () {
    $responses = [];
    for ($index = 0; $index <= 11; $index++) {
        $responses["https://example.com/{$index}"] = Http::response(
            [],
            301,
            ['Location' => '/'.($index + 1)],
        );
    }
    Http::fake($responses);

    $trace = (new HttpClientTracer(maxRedirects: 2))->trace(
        new RedirectUrl('https://example.com/0'),
    );

    expect($trace->failed())->toBeTrue()
        ->and($trace->error)->toContain('safety limit');
});
