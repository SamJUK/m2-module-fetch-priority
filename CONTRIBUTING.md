# Contributing

## Setup

This module is intended to run inside an existing Magento 2 install, at
`app/code/SamJUK/FetchPriority` (or via `composer require` in dev mode).

## Tests & Checks

Run these from the module directory before opening a PR:

```sh
make test-phpcs        # Magento2 coding standard
make test-phpstan      # Static analysis
make test-unit         # PHPUnit unit tests
make test-composer     # composer.json validation
```

`make local-tests` runs the local test suite (`_local_test.sh`).

CI runs lint, static analysis, and unit tests against Magento 2.4.6, 2.4.7
and 2.4.8 on every push/PR to `master`.

## Pull Requests

- Keep PRs focused on a single change.
- Add/update unit tests for behaviour changes.
- Use [Conventional Commits](https://www.conventionalcommits.org/) for commit
  messages (`feat:`, `fix:`, `chore:`, etc.) — this keeps history and
  releases readable.
- Make sure `make test-phpcs`, `make test-phpstan` and `make test-unit` pass
  before requesting review.

## Reporting Bugs

Open a GitHub issue with steps to reproduce, expected vs actual behaviour,
and your Magento/PHP version. For security issues, see
[SECURITY.md](SECURITY.md) instead of opening a public issue.
