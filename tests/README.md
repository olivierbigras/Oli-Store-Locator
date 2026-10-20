# Tests — Oli Store Locator

```bash
php tests/run-tests.php
composer install
vendor/bin/phpcs
```

The suite does not need WordPress. It covers CSV parsing, formula-injection neutralization, Status whitelist, geocode capabilities, per-IP limits (including autocomplete and ignored `X-Forwarded-For`), Sheets URL validation, sync lock/log, and the `fr_CA` `.po` / `.l10n.php` pair.

Regenerate PHP translations after editing a `.po`:

```bash
php bin/make-php.php fr_CA
```

(`wp i18n make-php languages/oli-store-locator-fr_CA.po` does the same job when WP-CLI is available.)

## Public Google Sheet for QA

This run could not create a new public Google Sheet (Drive sharing is limited to named users/domains, not “anyone with the link”).

Use Google’s official, stable public sample from the Sheets API quickstart:

- Edit / share URL: `https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/edit`
- Published CSV export: `https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/export?format=csv&gid=0`

That workbook is Google’s “Example Spreadsheet” (class data). It is valid for URL validation and a live fetch, but its columns are not store fields — use `tests/fixtures/sample-storepoint.csv` for import mapping QA.
