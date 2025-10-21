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
                ->useModel(Post::class),
            ReportAction::make('page-single-report')
                ->reporter(PostReporter::class)
                ->useRecord(Post::latest('title')->first()),
            ReportAction::make('page-group-report')
                ->reporter(GroupReporter::class),
            ReportAction::make('page-category-report')
                ->reporter(CategoryReporter::class)
                ->useRecord(Group::first()),
            ReportAction::make('page-categories-report')
                ->reporter(CategoryReporter::class)
                ->useModel(Category::class),
        ];
    }
}
