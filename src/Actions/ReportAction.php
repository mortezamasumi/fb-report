<?php

namespace Mortezamasumi\FbReport\Actions;

use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Collection;
use Livewire\Component;
use Mortezamasumi\FbReport\Concerns\CanCreateReport;
use Exception;

class ReportAction extends Action
{
    use CanCreateReport;

    public function getActionLabel(Component $livewire): string
    {
        if (method_exists($livewire, 'getResource')) {
            return $livewire->getResource()::getPluralModelLabel();
        }

        return '';
    }

    public function getActionHeading(Component $livewire): string
    {
        if (method_exists($livewire, 'getResource')) {
            return $livewire->getResource()::getPluralModelLabel();
        }

        return '';
    }

    // public function getActionRecords(Component $livewire, $action): Collection
    // {
    //     if (! $action->getRecord() && $livewire instanceof ListRecords) {
    //         throw new Exception('Action must be instance of ReportBulkAction');
    //     }

    //     $reporter = $this->getReporter();

    //     if ($livewire instanceof HasTable) {
    //         if (! $this->hasForceUseReporterModel()) {
    //             $query = $livewire->getTableQueryForExport();
    //         } else {
    //             $query = class_exists($reporter::getModel()) ? $reporter::getModel()::query() : null;
    //         }
    //     } else {
    //         $query = class_exists($reporter::getModel()) ? $reporter::getModel()::query() : null;
    //     }

    //     if ($query) {
    //         $query = $reporter::modifyQuery($query);
    //         if ($this->modifyQueryUsing) {
    //             $query = $this->evaluate($this->modifyQueryUsing, [
    //                 'query' => $query,
    //             ]) ?? $query;
    //         }

    //         $records = $query->get();
    //     } else {
    //         $records = collect([]);
    //     }

    //     return $records;
    // }
}
