<?php

declare(strict_types=1);

use App\Data\AuditData;
use App\Data\CaseResultData;
use App\Data\RedirectCaseData;
use App\Data\TraceData;
use App\Data\VerdictData;
use App\Data\ViolationData;
use App\Enums\ViolationType;
use App\ValueObjects\HttpStatus;
use App\ValueObjects\RedirectRange;
use App\ValueObjects\RedirectUrl;

function auditResult(int $id, bool $passed, int $group): CaseResultData
{
    $case = new RedirectCaseData(
        id: $id,
        group: $group,
        source: new RedirectUrl("https://example.com/{$id}"),
        expected: new RedirectUrl("https://example.com/{$id}"),
        range: RedirectRange::none(),
        redirectStatus: null,
        finalStatus: new HttpStatus(200),
    );

    return new CaseResultData(
        $case,
        new TraceData(collect()),
        new VerdictData($passed ? collect() : collect([
            new ViolationData(
                ViolationType::INCOMPLETE_TRACE,
                'Incomplete trace.',
            ),
        ])),
    );
}

it('calculates aggregate audit counts', function () {
    $audit = new AuditData(collect([
        auditResult(1, true, 5),
        auditResult(2, false, 5),
        auditResult(3, true, 4),
    ]));

    expect($audit->totalCount())->toBe(3)
        ->and($audit->passedCount())->toBe(2)
        ->and($audit->failedCount())->toBe(1)
        ->and($audit->passes())->toBeFalse();
});

it('filters results by group and rejects an empty audit as successful', function () {
    $audit = new AuditData(collect([
        auditResult(1, true, 5),
        auditResult(2, true, 4),
    ]));

    expect($audit->resultsByGroup(5)->count())->toBe(1)
        ->and((new AuditData(collect()))->passes())->toBeFalse();
});
