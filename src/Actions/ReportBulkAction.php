<?php

namespace Mortezamasumi\FbReport\Actions;

use Filament\Actions\BulkAction;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Collection;
use Livewire\Component;
use Mortezamasumi\FbReport\Concerns\CanCreateReport;

class ReportBulkAction extends BulkAction
{
    use CanCreateReport;

    public function getActionLabel(Component $livewire): string
    {
        return $this->getPluralModelLabel();
    }

    public function getActionHeading(Component $livewire): string
    {
        return $this->getPluralModelLabel();
    }

    // public function getActionRecords(Component $livewire, $action): Collection
    // {
    //     return $action->getSelectedRecords();

    // $reporter = $this->getReporter();

    // if ($livewire instanceof HasTable) {
    //     if (! $this->hasForceUseReporterModel()) {
    //         $query = $livewire->getTableQueryForExport();
    //     } else {
    //         $query = class_exists($reporter::getModel()) ? $reporter::getModel()::query() : null;
    //     }
    // } else {
    //     $query = class_exists($reporter::getModel()) ? $reporter::getModel()::query() : null;
    // }

    // if ($query) {
    //     $query = $reporter::modifyQuery($query);
    //     if ($this->modifyQueryUsing) {
    //         $query = $this->evaluate($this->modifyQueryUsing, [
    //             'query' => $query,
    //         ]) ?? $query;
    //     }

    //     $records = $action->getSelectedRecords();
    // } else {
    //     $records = collect([]);
    // }

    // return $records;
    // }
}
