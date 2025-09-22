<?php

namespace Mortezamasumi\FbReport\Actions;

use Filament\Actions\BulkAction;
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
}
