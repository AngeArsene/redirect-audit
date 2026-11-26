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

it('renders valid JSON for a successful audit', function () {
    $csv = base_path('tests/Fixtures/redirects/valid-direct-response.csv');

    Http::fake([
        'https://www.abc-immodiag.com/' => Http::response([], 200),
    ]);
    Http::preventStrayRequests();

    $this->artisan('redirects:check', [
        '--cases' => $csv,
        '--format' => 'json',
    ])
        ->expectsOutputToContain('"passed": 1')
        ->assertExitCode(0);
});

it('renders Markdown for a successful audit', function () {
    $csv = base_path('tests/Fixtures/redirects/valid-direct-response.csv');

    Http::fake([
        'https://www.abc-immodiag.com/' => Http::response([], 200),
    ]);
    Http::preventStrayRequests();

    $this->artisan('redirects:check', [
        '--cases' => $csv,
        '--format' => 'md',
    ])
        ->expectsOutputToContain('# Redirect Audit Report')
        ->assertExitCode(0);
});

it('returns an invalid-input exit code for a missing CSV', function () {
    $this->artisan('redirects:check', [
        '--cases' => 'missing.csv',
    ])
        ->expectsOutputToContain('CSV file not found')
        ->assertExitCode(2);
});

it('returns an invalid-input exit code for an unsupported format', function () {
    $csv = base_path('tests/Fixtures/redirects/valid-direct-response.csv');

    $this->artisan('redirects:check', [
        '--cases' => $csv,
        '--format' => 'xml',
    ])
        ->expectsOutputToContain('Unsupported report format')
        ->assertExitCode(2);
});
