<?php

namespace App\Actions;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Class FetchHttpResponse
 */
class FetchHttpResponse
{
    /**
     * Create a new action instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the action.
     */
    public function handle(string $url): Response
    {
        return Http::withoutRedirecting()->get($url);
    }
}
