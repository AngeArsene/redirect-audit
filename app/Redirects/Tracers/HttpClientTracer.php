<?php

declare(strict_types=1);

namespace App\Redirects\Tracers;

use App\Data\HopData;
use App\Data\TraceData;
use App\ValueObjects\HttpStatus;
use App\ValueObjects\RedirectUrl;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/** Traces HTTP redirect chains using manual redirect following. */
final class HttpClientTracer implements RedirectTracer
{
    public function __construct(private readonly int $maxRedirects = 10) {}

    public function trace(RedirectUrl $url): TraceData
    {
        $hops = collect();
        $visited = [];
        $current = $url->value;

        for ($redirectCount = 0; $redirectCount <= $this->maxRedirects; $redirectCount++) {
            if (isset($visited[$current])) {
                return new TraceData($hops, 'Redirect loop detected at ['.$current.'].');
            }

            $visited[$current] = true;

            try {
                $response = Http::withoutRedirecting()
                    ->connectTimeout(3)
                    ->timeout(10)
                    ->get($current);
            } catch (ConnectionException|GuzzleException $exception) {
                return new TraceData($hops, $exception->getMessage());
            }

            $hops->push($this->hopFromResponse($current, $response));
            $status = new HttpStatus((int) $response->status());

            if (! $status->isRedirect()) {
                return new TraceData($hops);
            }

            $location = $response->header('Location');
            if (! is_string($location) || trim($location) === '') {
                return new TraceData($hops, 'Missing Location header after redirect response.');
            }

            try {
                $resolved = $this->resolveLocation($current, $location);
                $destination = new RedirectUrl((string) $resolved);
            } catch (\Throwable $exception) {
                return new TraceData($hops, 'Invalid redirect location ['.$location.']: '.$exception->getMessage());
            }

            if ($redirectCount >= $this->maxRedirects) {
                return new TraceData($hops, 'Redirect safety limit of '.$this->maxRedirects.' exceeded.');
            }

            $current = $destination->value;
        }

        return new TraceData($hops, 'Redirect safety limit of '.$this->maxRedirects.' exceeded.');
    }

    private function hopFromResponse(string $url, Response $response): HopData
    {
        $location = $response->header('Location');

        return new HopData(
            url: new RedirectUrl($url),
            status: new HttpStatus((int) $response->status()),
            location: is_string($location) && trim($location) !== '' ? $location : null,
        );
    }

    private function resolveLocation(string $current, string $location): Uri
    {
        $location = trim($location);
        if (str_starts_with($location, '//')) {
            $scheme = parse_url($current, PHP_URL_SCHEME);
            $location = (is_string($scheme) ? $scheme : 'https').':'.$location;
        }

        $resolved = UriResolver::resolve(new Uri($current), new Uri($location));

        if (! in_array(strtolower($resolved->getScheme()), ['http', 'https'], true)) {
            throw new \InvalidArgumentException('Unsupported redirect protocol.');
        }

        $value = (string) $resolved;
        if (parse_url($value, PHP_URL_USER) !== null || parse_url($value, PHP_URL_PASS) !== null) {
            throw new \InvalidArgumentException('Redirect URLs with credentials are not allowed.');
        }

        return $resolved;
    }
}
