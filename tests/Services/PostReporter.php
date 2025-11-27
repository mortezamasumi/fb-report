<?php

namespace Mortezamasumi\FbReport\Tests\Services;

use Mortezamasumi\FbReport\Reports\ReportColumn;
use Mortezamasumi\FbReport\Reports\Reporter;

class PostReporter extends Reporter
{
    // protected bool $showHtml = true;
    public static bool $selectableColumns = false;

    public static function getColumns(): array
    {
        return [
            ReportColumn::make('__row__')
                ->span(1),  // to test span
            ReportColumn::make('title')
                ->localeDigit(),
            ReportColumn::make('created_at')
                ->jDate(),
            ReportColumn::make('updated_at')
                ->jDateTime(),
        ];
    }
}
