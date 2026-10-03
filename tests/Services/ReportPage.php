<?php

namespace Mortezamasumi\FbReport\Tests\Services;

use Filament\Pages\Page;
use Mortezamasumi\FbReport\Actions\ReportAction;

class ReportPage extends Page
{
    protected function getHeaderActions(): array
    {
        return [
            ReportAction::make('page-all-report')
                ->reporter(PostReporter::class)
                ->withLoadingScreen(false)
                ->useModel(Post::class),
            ReportAction::make('page-single-report')
                ->reporter(PostReporter::class)
                ->withLoadingScreen(false)
                ->useRecord(Post::latest('title')->first()),
            ReportAction::make('page-group-report')
                ->reporter(GroupReporter::class)
                ->withLoadingScreen(false),
            ReportAction::make('page-category-report')
                ->reporter(CategoryReporter::class)
                ->withLoadingScreen(false)
                ->useRecord(Group::first()),
            ReportAction::make('page-categories-report')
                ->reporter(CategoryReporter::class)
                ->withLoadingScreen(false)
                ->useModel(Category::class),
        ];
    }
}
