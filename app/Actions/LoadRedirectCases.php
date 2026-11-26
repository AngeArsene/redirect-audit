<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\RedirectCaseData;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use RuntimeException;

/** Loads and validates redirect audit cases from CSV. */
final class LoadRedirectCases
{
    public function handle(string $csv): Collection
    {
        if (! is_file($csv)) {
            throw new InvalidArgumentException("CSV file not found [{$csv}].");
        }

        if (! is_readable($csv)) {
            throw new InvalidArgumentException("CSV file is not readable [{$csv}].");
        }

        $file = new \SplFileObject($csv, 'r');
        $file->setFlags(
            \SplFileObject::READ_CSV
            | \SplFileObject::READ_AHEAD
            | \SplFileObject::SKIP_EMPTY
        );
        $file->setCsvControl(',', '"', '');

        $rows = collect(iterator_to_array($file));
        if ($rows->isEmpty()) {
            throw new InvalidArgumentException("CSV file is empty [{$csv}].");
        }

        $headers = array_map(
            static fn (mixed $header): string => trim((string) $header, "\xEF\xBB\xBF "),
            (array) $rows->shift(),
        );

        $required = [
            'id',
            'group',
            'source_url',
            'expected_url',
            'min_hops',
            'max_hops',
            'redirect_status',
            'final_status',
        ];

        if (count($headers) !== count(array_unique($headers))) {
            throw new InvalidArgumentException("Duplicate CSV headers in [{$csv}].");
        }

        $missing = array_diff($required, $headers);
        if ($missing !== []) {
            throw new InvalidArgumentException(
                "Missing CSV headers in [{$csv}]: ".implode(', ', $missing).'.',
            );
        }

        $seenIds = [];
        $rowNumber = 1;

        return $rows
            ->filter(static fn (array $row): bool => ! (
                count($row) === 1 && trim((string) $row[0]) === ''
            ))
            ->map(function (array $row) use ($csv, $headers, &$seenIds, &$rowNumber): RedirectCaseData {
                $rowNumber++;

                if (count($row) !== count($headers)) {
                    throw new InvalidArgumentException(
                        "Unexpected CSV row length in [{$csv}] at row {$rowNumber}: expected ".count($headers).', got '.count($row).'.',
                    );
                }

                $data = array_combine($headers, $row);
                if (! is_array($data)) {
                    throw new RuntimeException("Unable to map CSV row {$rowNumber} in [{$csv}].");
                }

                try {
                    $id = $this->strictInteger($data['id'], 'id', $csv, $rowNumber);
                    $group = $this->strictInteger($data['group'], 'group', $csv, $rowNumber);
                    $minHops = $this->strictInteger($data['min_hops'], 'min_hops', $csv, $rowNumber);
                    $maxHops = $this->strictInteger($data['max_hops'], 'max_hops', $csv, $rowNumber);
                    $finalStatus = $this->strictInteger($data['final_status'], 'final_status', $csv, $rowNumber);

                    if ($id < 0) {
                        throw new InvalidArgumentException("Invalid id [{$id}]; id must be non-negative.");
                    }

                    if (isset($seenIds[$id])) {
                        throw new InvalidArgumentException("Duplicate case id [{$id}].");
                    }
                    $seenIds[$id] = true;

                    if ($group < 1) {
                        throw new InvalidArgumentException("Invalid group [{$group}]; group must be positive.");
                    }

                    $redirectStatus = trim((string) $data['redirect_status']);
                    if ($redirectStatus !== '') {
                        $redirectStatus = (string) $this->strictInteger(
                            $redirectStatus,
                            'redirect_status',
                            $csv,
                            $rowNumber,
                        );
                    }

                    $data['id'] = (string) $id;
                    $data['group'] = (string) $group;
                    $data['min_hops'] = (string) $minHops;
                    $data['max_hops'] = (string) $maxHops;
                    $data['redirect_status'] = $redirectStatus;
                    $data['final_status'] = (string) $finalStatus;

                    $case = RedirectCaseData::fromArray($data);

                    if (($case->range->min === 0 && $case->range->max === 0) !== ($case->redirectStatus === null)) {
                        throw new InvalidArgumentException(
                            'Inconsistent redirect configuration; a zero redirect range requires an empty redirect_status.',
                        );
                    }

                    return $case;
                } catch (InvalidArgumentException $exception) {
                    throw new InvalidArgumentException(
                        "Invalid CSV data in [{$csv}] at row {$rowNumber}: {$exception->getMessage()}",
                        previous: $exception,
                    );
                }
            })
            ->values();
    }

    private function strictInteger(mixed $value, string $field, string $csv, int $row): int
    {
        $value = trim((string) $value);
        if (! preg_match('/^-?\d+$/', $value)) {
            throw new InvalidArgumentException(
                "Invalid {$field} [{$value}] in [{$csv}] at row {$row}; expected an integer.",
            );
        }

        return (int) $value;
    }
}
