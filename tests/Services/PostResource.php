<?php

namespace Mortezamasumi\FbReport\Tests\Services;

use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Mortezamasumi\FbReport\Actions\ReportAction;
use Mortezamasumi\FbReport\Actions\ReportBulkAction;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title1'),
            ])
            ->recordActions([
                ReportAction::make('record-report')
                    ->reporter(PostReporter::class)
                    ->selectableColumns(false)
            ])
            ->toolbarActions([
                ReportBulkAction::make('bulk-report')
                    ->reporter(PostReporter::class)
                    ->selectableColumns(false)
            ])
            ->headerActions([
                ReportAction::make('header-report')
                    ->reporter(PostReporter::class)
                    ->selectableColumns(false)
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosts::route('/'),
        ];
    }
}
