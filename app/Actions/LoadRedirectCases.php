<?php

namespace App\Actions;

use App\Data\RedirectCaseData;
use Illuminate\Support\Collection;

/**
 * Class LoadRedirectCases
 */
class LoadRedirectCases
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
    public function handle(string $csv): Collection
    {
        $file = new \SplFileObject($csv, 'r');
        $file->setFlags(
            \SplFileObject::READ_CSV
            | \SplFileObject::READ_AHEAD
            | \SplFileObject::SKIP_EMPTY
        );
        $file->setCsvControl(',', '"', '');

        $rows = collect(iterator_to_array($file));
        $headers = (array) $rows->shift();

        return $rows->map(
            fn (array $row): RedirectCaseData =>
                RedirectCaseData::fromArray(array_combine($headers, $row)),
        );
    }
}
