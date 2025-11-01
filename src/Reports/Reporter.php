<?php

namespace Mortezamasumi\FbReport\Reports;

use Filament\Support\Concerns\EvaluatesClosures;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Collection;
use Mortezamasumi\FbReport\Facades\FbReport;
use Closure;

abstract class Reporter
{
    use EvaluatesClosures;

    protected $html;
    /** @var array<ReportColumn> */
    protected array $cachedColumns;
    protected array|Collection|Model $record;
    protected static ?string $model = null;
    protected static string $view = 'fb-report::components.main';
    protected bool $showHtml = false;
    protected array|collection|Model|null $currentGroup = null;
    protected int|string|null $currentGroupIndex = null;
    protected array|collection|Model|null $currentSubGroup = null;
    protected int|string|null $currentSubGroupIndex = null;
    public static bool $selectableColumns = true;

    /**
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        protected Collection $records,
        protected string $returnUrl,
        protected array $selectedColumns,
        protected array $options,
        protected mixed $reportPageName,
    ) {
        $this->setRecords($records);

        FbReport::generateReport(
            reporter: $this,
            reportData: $this->getViewData(),
            reportConfig: $this->getConfig(),
        );
    }

    public function getGroupItems(): ?Collection
    {
        return null;
    }

    public function hasGroupItems(): bool
    {
        return (!! $this->getGroupItems()?->count()) ?? false;
    }

    public function getCurrentGroup(): array|collection|Model|null
    {
        return $this->currentGroup;
    }

    public function getSubGroupItems(): ?Collection
    {
        return null;
    }

    public function hasSubGroupItems(): bool
    {
        return (!! $this->getSubGroupItems()?->count()) ?? false;
    }

    public function setCurrentGroup(array|collection|Model|null $item): void
    {
        $this->currentGroup = $item;
    }

    public function setCurrentGroupIndex(int|string|null $index): void
    {
        $this->currentGroupIndex = $index;
    }

    public function getCurrentSubGroup(): array|collection|Model|null
    {
        return $this->currentSubGroup;
    }

    public function setCurrentSubGroup(array|collection|Model|null $item): void
    {
        $this->currentSubGroup = $item;
    }

    public function setCurrentSubGroupIndex(int|string|null $index): void
    {
        $this->currentSubGroupIndex = $index;
    }

    public function getTableRows(): Collection
    {
        if (! $this->getTableRowsData()) {
            return collect([]);
        }

        return $this
            ->getTableRowsData()
            ->map(
                function (Model|Collection|array|null $record, $index) {
                    $this->setRecord($record ?? []);

                    return collect($this->getColumnsData($this->getRowNumber($index)));
                }
            );
    }

    public function getTableRowsData(): Collection
    {
        return $this->getRecords();
    }

    public function getRowNumber(int|string $index): int|string
    {
        return (int) $index + 1;
    }

    public function getCachedColumns(): array
    {
        return $this->cachedColumns ?? array_reduce(
            static::getColumns(),
            function (array $carry, ReportColumn $column): array {
                $carry[$column->getName()] = $column->reporter($this);

                return $carry;
            },
            []
        );
    }

    public function getColumnsTitle(): Collection
    {
        $columns = $this->getCachedColumns();

        $data = [];

        foreach (array_keys($this->selectedColumns ?? []) as $column) {
            $data[] = [
                'width' => $columns[$column]->getSpanPercentage(),
                'text' => $columns[$column]->getLabel(),
            ];
        }

        return collect($data);
    }

    public function getColumnsData(int|string $sequenceNumber = ''): Collection
    {
        if (array_key_exists('__row__', $this->selectedColumns)) {
            $this->record['__row__'] = $sequenceNumber;
        }

        $columns = $this->getCachedColumns();

        $data = [];

        foreach (array_keys($this->selectedColumns ?? []) as $column) {
            $data[] = [
                'width' => $columns[$column]->getSpanPercentage(),
                'text' => $columns[$column]->getFormattedState(),
                'align' => $columns[$column]->getAlign(),
                'style' => $columns[$column]->getStyle(),
            ];
        }

        return collect($data);
    }

    public static function getOptionsFormComponents(): array
    {
        return [];
    }

    public function setModel(?string $model): void
    {
        static::$model = $model;
    }

    public static function getModel(): string
    {
        return static::$model ?? (string) str(class_basename(static::class))
            ->beforeLast('Reporter')
            ->prepend('App\\Models\\');
    }

    public function setRecords(array|Collection|null $records): void
    {
        $this->records = $records;
    }

    public function getRecords(): array|Collection|null
    {
        return $this->records;
    }

    public function setRecord(array|Collection|Model $record): void
    {
        $this->record = $record;
    }

    public function getRecord(): array|Collection|Model
    {
        return $this->record;
    }

    public function getOptions(): array
    {
        return $this->options ?? [];
    }

    public static function modifyQuery(Builder $query): Builder
    {
        return $query;
    }

    public static function getColumns(): array
    {
        return [];
    }

    public function getSelectedColumns(): array
    {
        $columns = $this->getCachedColumns();

        $data = [];

        foreach (array_keys($this->selectedColumns ?? []) as $column) {
            $data[] = $columns[$column];
        }

        return $data;
    }

    public function getColumnsSpan(): int
    {
        $total = 0;

        foreach ($this->getSelectedColumns() as $column) {
            if ($column instanceof ReportColumn) {
                $total += $column->getSpan();
            }
        }

        return $total;
    }

    public function getReturnUrl(): ?string
    {
        return $this->returnUrl;
    }

    public function getReportView(): string
    {
        return static::$view;
    }

    public function getViewData(): array
    {
        return [];
    }

    public function getConfig(): array
    {
        return [];
    }

    public function getShowHtml(): bool
    {
        return $this->showHtml;
    }

    public function getStyles($data): string|Htmlable
    {
        return '';
    }

    public function getGroupBeforeHtml($data): string|Htmlable
    {
        return '';
    }

    public function getGroupAfterHtml($data): string|Htmlable
    {
        return '';
    }

    public function getHtmlHead($data): string|Htmlable
    {
        return '';
    }

    public function getBeforeHtml($data): string|Htmlable
    {
        return '';
    }

    public function getAfterHtml($data): string|Htmlable
    {
        return '';
    }

    public function getReportTitle($data): string|Htmlable
    {
        return '';
    }

    public function getReportDescription($data): string|Htmlable
    {
        return '';
    }

    public function getReportHeader($data): string|Htmlable
    {
        return View::make('fb-report::components.header', compact('data'))->render();
    }

    public function getReportFooter($data): string|Htmlable
    {
        return View::make('fb-report::components.footer', compact('data'))->render();
    }

    public function getPageTitle(): string|Htmlable
    {
        return '';
    }

    public function getReportPageName(): ?string
    {
        return $this->reportPageName;
    }

    public function mpdfBeforHtml(LaravelMpdf $laravelMpdf): void
    {
        //
    }

    public function mpdfAfterHtml(LaravelMpdf $laravelMpdf): void
    {
        //
    }
}
