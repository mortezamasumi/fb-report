<?php

namespace Mortezamasumi\FbReport\Tests\Services;

use Mortezamasumi\FbReport\Reports\ReportColumn;
use Mortezamasumi\FbReport\Reports\Reporter;

class PostReporter extends Reporter
{
    protected bool $showHtml = true;

    public static function getColumns(): array
    {
        return [
            ReportColumn::make('__row__')
                ->localeDigit()
                ->span(1),
            ReportColumn::make('title1')
                ->localeDigit(),
            ReportColumn::make('title2'),
            ReportColumn::make('date1')
                ->jDate(),
            ReportColumn::make('date2')
                ->jDateTime(),
        ];
    }
}
