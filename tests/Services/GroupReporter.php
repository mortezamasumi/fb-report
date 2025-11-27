<?php

namespace Mortezamasumi\FbReport\Tests\Services;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Mortezamasumi\FbReport\Reports\ReportColumn;
use Mortezamasumi\FbReport\Reports\Reporter;

class GroupReporter extends Reporter
{
    // protected bool $showHtml = true;
    protected static ?string $model = Group::class;
    public static bool $selectableColumns = false;

    public function getGroupItems(): Collection
    {
        return $this->getRecords();
    }

    public function getSubGroupItems(): Collection
    {
        return $this->getCurrentGroup()->categories;
    }

    public function getTableRowsData(): Collection
    {
        return $this->getCurrentSubGroup()->posts;
    }

    public function getBeforeHtml($data): string|Htmlable
    {
        return new HtmlString($this->getRecord()->category->group->title . ' ' . $this->getRecord()->category->title);
    }

    public static function getColumns(): array
    {
        return [
            ReportColumn::make('title'),
        ];
    }
}
