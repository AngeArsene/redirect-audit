<?php

use Illuminate\Support\Facades\Http;

it('passes when the source URL returns 200 without redirects', function () {

    $csv = base_path('tests/Fixtures/redirects/valid-direct-response.csv');

    Http::fake(['https://www.abc-immodiag.com/' => Http::response(['status' => 'ok'], 200)]);

    Http::preventStrayRequests();

    $this->artisan('redirects:check', ['--cases' => $csv])
        ->expectsOutputToContain('PASS')
        ->assertExitCode(0);

    Http::assertSentCount(1);
});
