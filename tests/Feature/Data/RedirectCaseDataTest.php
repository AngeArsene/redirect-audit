<?php

use App\Data\RedirectCaseData;
use App\ValueObjects\HttpStatus;
use App\ValueObjects\RedirectRange;
use App\ValueObjects\RedirectUrl;

it('creates a redirect case with typed properties', function () {
    $group = random_int(1, 5);
    $source = new RedirectUrl('https://example.com/old');
    $expected = new RedirectUrl('https://example.com/new');
    $range = RedirectRange::between(1, 3);
    $redirectStatus = new HttpStatus(301);
    $finalStatus = new HttpStatus(200);

    $case = new RedirectCaseData(
        id: 1,
        group: $group,
        source: $source,
        expected: $expected,
        range: $range,
        redirectStatus: $redirectStatus,
        finalStatus: $finalStatus,
    );

    expect($case->id)->toBe(1)
        ->and($case->group)->toBe($group)
        ->and($case->source)->toBe($source)
        ->and($case->expected)->toBe($expected)
        ->and($case->range)->toBe($range)
        ->and($case->redirectStatus)->toBe($redirectStatus)
        ->and($case->finalStatus)->toBe($finalStatus);
});
