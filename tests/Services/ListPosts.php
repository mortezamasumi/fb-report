<?php

namespace Mortezamasumi\FbReport\Tests\Services;

use Filament\Resources\Pages\ListRecords;
use Mortezamasumi\FbReport\Actions\ReportAction;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ReportAction::make('list-report')
                ->reporter(PostReporter::class)
                ->withLoadingScreen(false),
            ReportAction::make('loading-report')
                ->reporter(PostReporter::class),
            ReportAction::make('synchronous-report')
                ->reporter(PostReporter::class)
                ->withLoadingScreen(false),
            ReportAction::make('failing-loading-report')
                ->reporter(FailingPostReporter::class),
        ];
    }
}
