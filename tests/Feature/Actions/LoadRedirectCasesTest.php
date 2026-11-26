<?php

use App\Actions\LoadRedirectCases;
use Illuminate\Support\Collection;

it('loads audit cases from a CSV file', function () {

    $csv = base_path('tests/Fixtures/redirects/valid-direct-response.csv');
    $action = new LoadRedirectCases();
    $cases = $action->handle($csv);

    expect($cases)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(1);

    expect($cases->first()['source_url'])->toBe('https://www.abc-immodiag.com/');
    expect($cases->first()['expected_url'])->toBe('https://www.abc-immodiag.com/');
    expect($cases->first()['min_hops'])->toBe('0');
    expect($cases->first()['max_hops'])->toBe('0');
    expect($cases->first()['redirect_status'])->toBe('');
    expect($cases->first()['final_status'])->toBe('200');
});
