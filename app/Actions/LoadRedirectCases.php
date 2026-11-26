<?php

namespace App\Actions;

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
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY);
        $file->setCsvControl(',', '"', '');

        $rows = collect(iterator_to_array($file));
        $headers = (array) $rows->shift();

        return $rows->map(
            fn (array $row) => array_combine($headers, $row),
        );
    }
}
