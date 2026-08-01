# Changelog

All notable changes to `fb-report` will be documented in this file.

## 5.1.0 - 2026-08-01

- Bring package up to workspace standard: pint, phpstan, CI gates, config file, composer metadata.
- Add `config/fb-report.php` with PDF password, font directory, and font definitions.
- Remove dead code: `CanCreateReport1111`, `ReportMacroServiceProvider` (duplicate macros), `writeChunkedHtmlToPdf()`, commented-out methods.
- Fix `LaravelMpdf` fontDir null guard and `Reporter` constructor type widening.
- Update CI workflow to match workspace standard with validate, audit, pint, phpstan, and prefer-lowest matrix.

## 1.0.0 - 202X-XX-XX

- initial release
