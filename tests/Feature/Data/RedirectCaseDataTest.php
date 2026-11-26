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

it('creates a redirect case from CSV data', function () {
    $row = [
        'id' => '1',
        'group' => '5',
        'source_url' => 'https://www.abc-immodiag.com/',
        'expected_url' => 'https://www.abc-immodiag.com/',
        'min_hops' => '0',
        'max_hops' => '0',
        'redirect_status' => '',
        'final_status' => '200',
    ];

    $case = RedirectCaseData::fromArray($row);

    expect($case->id)->toBe(1)
        ->and($case->group)->toBe(5)
        ->and($case->source)->toEqual(new RedirectUrl('https://www.abc-immodiag.com/'))
        ->and($case->expected)->toEqual(new RedirectUrl('https://www.abc-immodiag.com/'))
        ->and($case->range)->toEqual(RedirectRange::between(0, 0))
        ->and($case->redirectStatus)->toBeNull()
        ->and($case->finalStatus)->toEqual(new HttpStatus(200));
});
