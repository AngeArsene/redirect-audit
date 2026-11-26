<?php

declare(strict_types=1);

use App\Actions\LoadRedirectCases;
use App\Data\RedirectCaseData;
use App\ValueObjects\HttpStatus;
use App\ValueObjects\RedirectRange;
use App\ValueObjects\RedirectUrl;
use Illuminate\Support\Collection;

it('loads audit cases from a CSV file', function () {
    $csv = base_path('tests/Fixtures/redirects/valid-direct-response.csv');
    $action = new LoadRedirectCases;
    $cases = $action->handle($csv);

    expect($cases)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(1);

    $firstCase = $cases->first();

    expect($firstCase)
        ->toBeInstanceOf(RedirectCaseData::class)
        ->and($firstCase->id)->toBe(1)
        ->and($firstCase->group)->toBe(5)
        ->and($firstCase->source)->toEqual(new RedirectUrl('https://www.abc-immodiag.com/'))
        ->and($firstCase->expected)->toEqual(new RedirectUrl('https://www.abc-immodiag.com/'))
        ->and($firstCase->range)->toEqual(RedirectRange::between(0, 0))
        ->and($firstCase->redirectStatus)->toBeNull()
        ->and($firstCase->finalStatus)->toEqual(new HttpStatus(200));
});
