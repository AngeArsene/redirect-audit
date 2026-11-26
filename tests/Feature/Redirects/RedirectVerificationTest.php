<?php

declare(strict_types=1);

use App\Actions\VerifyRedirect;
use App\Data\HopData;
use App\Data\RedirectCaseData;
use App\Data\TraceData;
use App\Enums\ViolationType;
use App\Redirects\Checks\DestinationCheck;
use App\Redirects\Checks\FinalStatusCheck;
use App\Redirects\Checks\RedirectCountCheck;
use App\Redirects\Checks\RedirectStatusCheck;
use App\ValueObjects\HttpStatus;
use App\ValueObjects\RedirectRange;
use App\ValueObjects\RedirectUrl;

function redirectVerificationCase(): RedirectCaseData
{
    return new RedirectCaseData(
        id: 1,
        group: 1,
        source: new RedirectUrl('http://example.com/start'),
        expected: new RedirectUrl('https://example.com/final'),
        range: RedirectRange::exactly(1),
        redirectStatus: new HttpStatus(301),
        finalStatus: new HttpStatus(200),
    );
}

function redirectVerificationVerifier(): VerifyRedirect
{
    return new VerifyRedirect([
        new RedirectCountCheck,
        new RedirectStatusCheck,
        new DestinationCheck,
        new FinalStatusCheck,
    ]);
}

it('passes a correct redirect chain', function () {
    $trace = new TraceData(collect([
        new HopData(
            url: new RedirectUrl('http://example.com/start'),
            status: new HttpStatus(301),
            location: 'https://example.com/final',
        ),
        new HopData(
            url: new RedirectUrl('https://example.com/final'),
            status: new HttpStatus(200),
        ),
    ]));

    $verdict = redirectVerificationVerifier()->handle(redirectVerificationCase(), $trace);

    expect($verdict->passes())->toBeTrue()
        ->and($verdict->violations)->toBeEmpty();
});

it('reports multiple independent violations', function () {
    $trace = new TraceData(collect([
        new HopData(
            url: new RedirectUrl('http://example.com/start'),
            status: new HttpStatus(302),
            location: 'https://example.com/wrong',
        ),
        new HopData(
            url: new RedirectUrl('https://example.com/wrong'),
            status: new HttpStatus(404),
        ),
    ]));

    $verdict = redirectVerificationVerifier()->handle(redirectVerificationCase(), $trace);

    expect($verdict->fails())->toBeTrue()
        ->and($verdict->violations->pluck('type')->all())->toContain(
            ViolationType::WRONG_REDIRECT_STATUS,
            ViolationType::WRONG_DESTINATION,
            ViolationType::WRONG_FINAL_STATUS,
        );
});

it('fails a missing required redirect', function () {
    $trace = new TraceData(collect([
        new HopData(
            url: new RedirectUrl('http://example.com/start'),
            status: new HttpStatus(200),
        ),
    ]));

    $verdict = redirectVerificationVerifier()->handle(redirectVerificationCase(), $trace);

    expect($verdict->violations->pluck('type')->all())
        ->toContain(ViolationType::REDIRECT_COUNT_MISMATCH);
});

it('does not evaluate destination or final status for incomplete traces', function () {
    $trace = new TraceData(
        collect([
            new HopData(
                url: new RedirectUrl('http://example.com/start'),
                status: new HttpStatus(301),
                location: '/missing',
            ),
        ]),
        'Missing Location header',
    );

    $verdict = redirectVerificationVerifier()->handle(redirectVerificationCase(), $trace);

    expect($verdict->fails())->toBeTrue()
        ->and($verdict->violations->pluck('type')->all())->toContain(
            ViolationType::MISSING_LOCATION,
            ViolationType::INCOMPLETE_TRACE,
        )
        ->and($verdict->violations->pluck('type')->contains(ViolationType::WRONG_DESTINATION))->toBeFalse();
});
