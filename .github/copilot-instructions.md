# Redirect Audit

## Commands

- PHP 8.3 or newer and Composer are required. Install dependencies with `composer install`.
- Run the CLI from source on Windows with `php redirect-audit <command>`. `php redirect-audit list` is the quickest way to discover currently registered commands.
- Run the full Pest suite with `php vendor/bin/pest`.
- Run one test file with `php vendor/bin/pest tests/Feature/ExampleTest.php`.
- Filter tests by name with `php vendor/bin/pest --filter="redirect"` or `php redirect-audit test --filter="redirect"`.
- Run tests from one PHPUnit suite with `php vendor/bin/pest --testsuite=Feature` or `php vendor/bin/pest --testsuite=Unit`.
- Check formatting without changing files with `php vendor/bin/pint --test`.
- Format source files with `php vendor/bin/pint`.
- Build the standalone executable with `php redirect-audit app:build <name> --build-version=<version>`.

There are currently no Composer scripts, so use the binaries and Laravel Zero commands directly. The repository currently has no test files and Pest reports "No tests found".

## Architecture

- This is a Laravel Zero console application, not a web application. `composer.json` names the package `ange-arsene/redirect-audit` and identifies the intended purpose as automated HTTP redirect auditing.
- `redirect-audit` is the executable entry script. It loads Composer, creates the Laravel Zero application from `bootstrap/app.php`, and dispatches the console kernel.
- `bootstrap/app.php` configures the Laravel Zero application at the repository root. `bootstrap/providers.php` is the application provider bootstrap list.
- `config/app.php` defines the application name and provider list. `config/commands.php` defines command discovery from `app/Commands`, as well as hidden, removed, and added framework commands.
- `app/Providers/AppServiceProvider.php` is the place for application service bindings and bootstrapping.
- Console commands should live under `app/Commands` and extend the Laravel console command base. Registering a command in `config/commands.php` is not normally needed for classes in the configured `app/Commands` path.
- The project includes Laravel HTTP and Guzzle dependencies for HTTP work. Preserve the Laravel Zero/Illuminate conventions rather than introducing a separate application framework.
- `box.json` controls standalone PHAR builds. Keep runtime dependencies in the directories and files listed there when changing the packaged layout.
- Pest is configured by `phpunit.xml.dist` with Feature and Unit suites. `tests/Pest.php` applies `Tests\TestCase` to feature tests; unit tests use Pest/PHPUnit conventions unless the test setup is changed.
- `.github/workflows/release.yml` runs semantic-release from `main` and publishes GitHub releases. Commit subjects must therefore remain compatible with semantic-release conventional commit parsing.

## Project Conventions

- Target PHP 8.3+ and PSR-4: `App\` maps to `app/`, `Tests\` maps to `tests/`, and database namespaces map to `database/`.
- Follow `.editorconfig`: UTF-8, LF line endings, four-space indentation, and a final newline. Markdown is exempt from trailing-whitespace trimming.
- Use Laravel/Pest-style command and test organization. Generate new commands and tests with `php redirect-audit make:command <Name>` and `php redirect-audit make:test <Name>`, rather than manually copying framework scaffolding.
- Prefer feature tests for command behavior and unit tests for isolated redirect-analysis logic. Pest feature tests inherit Laravel Zero's `Tests\TestCase`.
- Do not edit `vendor/`, generated Composer metadata, or framework package files.
- Keep CLI output and exit behavior testable through Laravel Zero's command testing support. Exit status and console output are part of the command contract.
