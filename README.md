# Redirect Audit

An automated HTTP redirect auditing CLI built with PHP 8.3+ and Laravel Zero.

## What the app does

Redirect Audit is a Laravel Zero console application intended to inspect HTTP redirect chains. The executable entry point is [`redirect-audit`](redirect-audit), and application commands are discovered from [`app/Commands`](app/Commands). No redirect-audit command or HTTP audit workflow is implemented yet; the repository is currently a Laravel Zero skeleton.

## Features

- Laravel Zero console bootstrapping through [`bootstrap/app.php`](bootstrap/app.php).
- Command discovery from [`config/commands.php`](config/commands.php), with application commands placed under [`app/Commands`](app/Commands).
- Laravel HTTP and Guzzle dependencies available for implementing redirect requests.
- Standalone executable builds configured by [`box.json`](box.json).
- Pest feature and unit suites configured in [`phpunit.xml.dist`](phpunit.xml.dist).
- Laravel Pint formatting configured for PHP source files.

## Project structure

```text
redirect-audit/                 # PHP executable entry point
app/
  Providers/AppServiceProvider.php
bootstrap/
  app.php                       # Laravel Zero application bootstrap
  providers.php                 # Application provider registration
config/
  app.php                       # Application name and providers
  commands.php                  # Command discovery and framework command configuration
tests/
  Pest.php                      # Pest configuration
  TestCase.php                  # Laravel Zero feature test base
box.json                        # Standalone executable build configuration
composer.json                   # PHP dependencies and package metadata
phpunit.xml.dist                # Pest/PHPUnit suites
.github/workflows/release.yml   # Semantic-release workflow
```

## Setup

Requires PHP 8.3 or newer and Composer.

```powershell
composer install
php redirect-audit list
```

No `.env.example`, database configuration, migration, seeding, or frontend build setup is currently present.

## Usage

```powershell
# Discover commands
php redirect-audit list

# Run the complete test suite
php vendor/bin/pest

# Run one test file
php vendor/bin/pest tests/Feature/ExampleTest.php

# Filter tests by name
php vendor/bin/pest --filter="redirect"

# Check PHP formatting without changing files
php vendor/bin/pint --test

# Format PHP files
php vendor/bin/pint

# Build a standalone executable
php redirect-audit app:build <name> --build-version=<version>
```

There are currently no test files or Composer scripts.

_Repository has no commits yet; status footer unavailable._
