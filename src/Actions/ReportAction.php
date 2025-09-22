<?php

namespace Mortezamasumi\FbReport\Actions;

use Filament\Actions\Action;
use Livewire\Component;
use Mortezamasumi\FbReport\Concerns\CanCreateReport;

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
}
