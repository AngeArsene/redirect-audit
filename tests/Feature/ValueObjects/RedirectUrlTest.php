<?php

use App\ValueObjects\RedirectUrl;

dataset('valid urls', [
    'http root' => ['http://example.com/'],
    'https root' => ['https://example.com/'],
    'https www root' => ['https://www.example.com/'],
    'path with trailing slash' => ['https://example.com/contact/'],
    'path without trailing slash' => ['https://example.com/contact'],
    'query parameter' => ['https://example.com/?id=5'],
]);

it('accepts a valid HTTP or HTTPS URL', function (string $url) {
    $redirectUrl = new RedirectUrl($url);

    expect($redirectUrl->value)->toBe($url);
})->with('valid urls');

dataset('invalid redirect urls', [
    'empty string' => [''],
    'whitespace only' => ['   '],
    'missing scheme' => ['example.com/path'],
    'protocol relative url' => ['//example.com/path'],
    'unsupported ftp scheme' => ['ftp://example.com/file'],
    'javascript pseudo scheme' => ['javascript:alert(1)'],
    'data uri' => ['data:text/html,<h1>hi</h1>'],
    'missing host' => ['https://'],
    'space in url' => ['https://example.com/bad path'],
]);

it('rejects an invalid redirect URL', function (string $url) {
    expect(fn () => new RedirectUrl($url))->toThrow(InvalidArgumentException::class);
})->with('invalid redirect urls');

dataset('redirect url equality cases', [
    'identical urls' => ['https://example.com/', 'https://example.com/', true],
    'different schemes' => ['http://example.com/', 'https://example.com/', false],
    'different host subdomain' => ['https://example.com/', 'https://www.example.com/', false],
    'trailing slash vs no trailing slash' => ['https://example.com/page/', 'https://example.com/page', false],
    'different query parameters' => ['https://example.com/?id=1', 'https://example.com/?id=2', false],
]);

it('determines whether two redirect URLs are strictly equal', function (string $firstUrl, string $secondUrl, bool $expected) {
    $firstRedirectUrl = new RedirectUrl($firstUrl);
    $secondRedirectUrl = new RedirectUrl($secondUrl);

    expect($firstRedirectUrl->equals($secondRedirectUrl))->toBe($expected);
})->with('redirect url equality cases');
