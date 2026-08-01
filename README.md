# fb-report

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mortezamasumi/fb-report.svg?style=flat-square)](https://packagist.org/packages/mortezamasumi/fb-report)
[![CI Tests](https://github.com/mortezamasumi/fb-report/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/mortezamasumi/fb-report/actions/workflows/ci.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/mortezamasumi/fb-report.svg?style=flat-square)](https://packagist.org/packages/mortezamasumi/fb-report)
[![License](https://img.shields.io/packagist/l/mortezamasumi/fb-report.svg?style=flat-square)](LICENSE.md)

Generate PDF reports using mPDF for Filament tables and bulk actions.

## Features

- Generate PDF reports from Filament table data with a single `->reporter()` call.
- Support for grouped and sub-grouped data across multiple pages.
- Persian/Arabic font support out of the box with 24 bundled fonts.
- Configurable page format, orientation, margins, and PDF protection password.
- Column formatting helpers for Jalali dates and locale-aware digits.

## Installation

```bash
composer require mortezamasumi/fb-report
```

## Configuration

You can publish the config file with:

```bash
php artisan vendor:publish --tag="fb-report-config"
```

The published `config/fb-report.php` lets you set the default PDF password, font directory, and bundled font definitions.

## Usage

Create a reporter class extending `Reporter` and implement `getColumns()`, `getGroupItems()`, and `getTableRowsData()`:

```php
use Mortezamasumi\FbReport\Reports\Reporter;

class StudentRegisterFormReporter extends Reporter
{
    public function getConfig(): array
    {
        return [
            'margin_top' => 0,
            'margin_right' => 5,
            'margin_left' => 5,
            'margin_bottom' => 0,
        ];
    }

    protected function getGroupItems(): ?Collection
    {
        return $this->getRecords();
    }

    public function getTableRowsData(): Collection
    {
        return $this->getRecords();
    }
}
```

Then attach it to a Filament table or bulk action:

```php
Table::make()
    ->reporter(StudentRegisterFormReporter::class)
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
