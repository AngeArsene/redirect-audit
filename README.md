# Redirect Audit

Redirect Audit is a Laravel Zero CLI that observes HTTP redirect behavior and compares it with strongly typed audit cases loaded from CSV.

## Requirements

- PHP 8.3 or newer
- Composer
- Network access to the URLs being audited

## Installation

```powershell
composer install
php redirect-audit list
```

## Audit a CSV

```powershell
php redirect-audit redirects:check --cases=path\to\cases.csv
php redirect-audit redirects:check --cases=path\to\cases.csv --test=5
php redirect-audit redirects:check --cases=path\to\cases.csv --format=md
php redirect-audit redirects:check --cases=path\to\cases.csv --format=json --output=reports\audit.json
```

The `--cases` option is required. `--test` accepts one or more comma-separated group numbers. `--format` accepts `table`, `md`, or `json`. `--output` writes the report to a new external file and does not overwrite an existing file. `--fail-fast` stops after the first failed case.

Exit codes:

- `0`: audit completed and every selected case passed
- `1`: one or more selected cases failed
- `2`: invalid input or an inability to execute the audit

## CSV schema

```csv
id,group,source_url,expected_url,min_hops,max_hops,redirect_status,final_status
1,5,https://www.example.com/,https://www.example.com/,0,0,,200
2,1,http://legacy.example.com/page,https://www.example.com/page,1,1,301,200
```

Required fields:

- `id`: unique non-negative case identifier
- `group`: positive audit test group
- `source_url`: absolute HTTP or HTTPS URL
- `expected_url`: exact destination URL
- `min_hops` and `max_hops`: inclusive redirect count range
- `redirect_status`: expected redirect status, or blank when redirects are prohibited
- `final_status`: expected final HTTP status

The CSV loader rejects missing headers, duplicate headers, duplicate IDs, malformed rows and invalid numeric values before network requests start.

## Audit rules

A case passes when:

- the observed redirect count is within the configured range
- every observed redirect status matches `redirect_status`
- the final observed URL exactly matches `expected_url`
- the final HTTP status matches `final_status`
- tracing has no network error, loop, missing Location header, invalid destination or safety-limit failure

URLs are compared literally. Scheme, host, path, query and trailing-slash differences are not normalized.

## Project structure

```text
app/
  Actions/           CSV loading and audit orchestration
  Commands/          Laravel Zero console entry points
  Data/              Immutable audit result DTOs
  Enums/             Violation and report types
  Redirects/
    Checks/          Independent redirect verification rules
    Reports/         Table, Markdown and JSON renderers
    Tracers/         HTTP redirect tracing contract and implementation
  ValueObjects/      Validated HTTP status, range and URL values
tests/
  Feature/           Framework and CLI behavior
  Fakes/             Deterministic test collaborators
```

## Development

```powershell
php vendor/bin/pest
php vendor/bin/pest tests/Feature/Commands/CheckRedirectsCommandTest.php
php vendor/bin/pest --filter="redirect"
php vendor/bin/pint --test
php vendor/bin/pint
```

The HTTP tracer uses manual redirect following and never enables Laravel HTTP redirect middleware. Tracing has a 10-hop default safety limit, 3-second connection timeout and 10-second request timeout.

## Extending the application

- Add a verification rule by implementing `App\Redirects\Checks\RedirectCheck` and registering it in `AppServiceProvider`.
- Add a tracer implementation by implementing `App\Redirects\Tracers\RedirectTracer`; bind it in `AppServiceProvider`.
- Add a report format by implementing `App\Redirects\Reports\AuditReport` and extending `ReportFormat` and `RenderAuditReport`.

## Known limitations

The genuine 60-case ABC Immodiag CSV is not included because the original URL mappings were not supplied. The repository therefore provides the generic engine and clearly identified test fixtures, but no claim is made that a production 60-case audit has been executed.
