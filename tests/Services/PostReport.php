<?php

namespace Mortezamasumi\FbReport\Tests\Services;

use Filament\Pages\Page;
use Mortezamasumi\FbReport\Actions\ReportAction;

class PostReport extends Page
{
    protected function getHeaderActions(): array
    {
        return [
            ReportAction::make('report')
                ->reporter(PostReporter::class)
                ->selectableColumns(false)
                ->useRecord(Post::all()->skip(2)->first())
        ];
    }
}
