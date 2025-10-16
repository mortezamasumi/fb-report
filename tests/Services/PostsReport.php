<?php

namespace Mortezamasumi\FbReport\Tests\Services;

use Filament\Pages\Page;
use Mortezamasumi\FbReport\Actions\ReportAction;

class PostsReport extends Page
{
    protected function getHeaderActions(): array
    {
        return [
            ReportAction::make('report')
                ->reporter(PostReporter::class)
                ->selectableColumns(false)
                ->useModel(Post::class)
        ];
    }
}
