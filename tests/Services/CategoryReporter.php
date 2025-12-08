<?php

namespace Mortezamasumi\FbReport\Tests\Services;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Mortezamasumi\FbReport\Reports\ReportColumn;
use Mortezamasumi\FbReport\Reports\Reporter;

class CategoryReporter extends Reporter
{
    public static bool $selectableColumns = false;

    public function getTableRowsData(): Collection
    {
        if ($this->hasSubGroupItems()) {
            return $this->getCurrentSubGroup()->posts;
        }

        return $this->getCurrentGroup()->posts;
    }

    public function getGroupItems(): Collection
    {
        return $this->getRecords();
    }

    public function getSubGroupItems(): Collection
    {
        if ($this->getCurrentGroup() instanceof Category) {
            return collect([]);
        }

        return $this->getCurrentGroup()->categories;
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
