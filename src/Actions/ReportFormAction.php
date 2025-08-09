<?php

namespace Mortezamasumi\FbReport\Actions;

use Filament\Actions\Action;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Collection;
use Livewire\Component;
use Mortezamasumi\FbReport\Concerns\CanCreateReport;

class ReportFormAction extends Action
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

    public function getActionRecords(Component $livewire, $action): Collection
    {
        $reporter = $this->getReporter();

        if ($livewire instanceof HasTable) {
            if (! $this->hasForceUseReporterModel()) {
                $query = $livewire->getTableQueryForExport();
            } else {
                $query = class_exists($reporter::getModel()) ? $reporter::getModel()::query() : null;
            }
        } else {
            $query = class_exists($reporter::getModel()) ? $reporter::getModel()::query() : null;
        }

        if ($query) {
            $query = $reporter::modifyQuery($query);
            if ($this->modifyQueryUsing) {
                $query = $this->evaluate($this->modifyQueryUsing, [
                    'query' => $query,
                ]) ?? $query;
            }

            $records = $query->get();
        } else {
            $records = collect([]);
        }

        return $records;
    }
}
