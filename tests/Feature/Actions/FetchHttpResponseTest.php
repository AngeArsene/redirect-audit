<?php

use App\Actions\FetchHttpResponse;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

it('fetches an HTTP response without following redirects', function () {

    $url = 'https://www.abc-immodiag.com/';
    Http::fake([$url => Http::response(['status' => 'ok'], 200)]);

    $action = new FetchHttpResponse;
    $response = $action->handle($url);

    expect($response->status())->toBe(200);

    Http::assertSentCount(1);
});

it('returns a redirect response without following its destination', function () {

    $url = 'https://abc-immodiag.com/';
    Http::fake([
        $url => Http::response(['status' => 'Moved Permanently'],
            301,
            ['Location' => 'https://www.abc-immodiag.com/']),
    ]);

    $action = new FetchHttpResponse;
    $response = $action->handle($url);

    expect($response->status())->toBe(301);
    expect($response->header('Location'))->toBe('https://www.abc-immodiag.com/');

    Http::assertSentCount(1);
});
