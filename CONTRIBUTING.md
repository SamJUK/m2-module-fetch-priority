# Contributing

## Setup

This module is intended to run inside an existing Magento 2 install, at
`app/code/SamJUK/FetchPriority` (or via `composer require` in dev mode).

## Tests & Checks

Run these from the module directory before opening a PR:

```sh
composer install
vendor/bin/lefthook install            # Conventional Commits, PHPCS and PHPStan on every commit
vendor/bin/samjuk-dev-standards check  # PHPCS and PHPStan over the whole module
vendor/bin/phpunit Test/Unit           # unit tests; the SetCollection ones need Magento's generated factories, so run in CI
```

CI runs lint, PHPCS, PHPStan, a declared-dependency check (ExtensionChecker), then unit tests, DI compile and integration tests on every supported Magento and Mage-OS version, on every push and PR to `master`.

## Pull Requests

- Keep PRs focused on a single change.
- Add/update unit tests for behaviour changes.
- Use [Conventional Commits](https://www.conventionalcommits.org/) for commit
  messages (`feat:`, `fix:`, `chore:`, etc.) — this keeps history and
  releases readable.
- Make sure `vendor/bin/samjuk-dev-standards check` and the unit tests pass
  before requesting review.

## Reporting Bugs

Open a GitHub issue with steps to reproduce, expected vs actual behaviour,
and your Magento/PHP version. For security issues, see
[SECURITY.md](SECURITY.md) instead of opening a public issue.
