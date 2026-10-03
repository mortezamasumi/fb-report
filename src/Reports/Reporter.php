<?php

namespace Mortezamasumi\FbReport\Reports;

use Filament\Support\Concerns\EvaluatesClosures;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Mortezamasumi\FbReport\Facades\FbReport;

abstract class Reporter
{
    use EvaluatesClosures;

    // -------------------------------------------------------------------------
    // Properties
    // -------------------------------------------------------------------------

    protected $html;

    /** @var array<ReportColumn> */
    protected array $cachedColumns;

    protected array|Collection|Model $record;

    protected static ?string $model = null;

    protected static string $view = 'fb-report::components.main';

    protected bool $showHtml = false;

    protected array|Collection|Model|null $currentGroup = null;

    protected int|string|null $currentGroupIndex = null;

    protected array|Collection|Model|null $currentSubGroup = null;

    protected int|string|null $currentSubGroupIndex = null;

    public static bool $selectableColumns = true;

    // -------------------------------------------------------------------------
    // Constructor
    // -------------------------------------------------------------------------

    /**
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        protected array|Collection|null $records,
        protected string $returnUrl,
        protected array $selectedColumns,
        protected array $options,
        protected mixed $reportPageName,
        protected bool $showLoadingScreen = true,
    ) {
        $this->setRecords($records);

        FbReport::generateReport(
            reporter: $this,
            reportData: $this->getViewData(),
            reportConfig: $this->getConfig(),
            showLoadingScreen: $this->showLoadingScreen,
        );
    }

    // -------------------------------------------------------------------------
    // Core Configuration (To be implemented by child)
    // -------------------------------------------------------------------------

    /**
     * @return array<ReportColumn>
     */
    public static function getColumns(): array
    {
        return [];
    }

    public static function getModel(): string
    {
        return static::$model ?? (string) str(class_basename(static::class))
            ->beforeLast('Reporter')
            ->prepend('App\\Models\\');
    }

    public static function getOptionsFormComponents(): array
    {
        return [];
    }

    public static function modifyQuery(Builder $query): Builder
    {
        return $query;
    }

    // -------------------------------------------------------------------------
    // Main Report Rendering
    // -------------------------------------------------------------------------

    /**
     * @param  array<string, mixed>  $data
     */
    public function pageContent(Mpdf $mpdf, array $data): void
    {
        $titles = $this->getColumnsTitle();
        $rows = $this->getTableRows();

        $this->pageBefore($mpdf, $data);
        $this->writeHtml($mpdf, $this->getMainHtml($data, $titles, $rows));
        $this->pageAfter($mpdf, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function makeContent(Mpdf $mpdf, array $data): void
    {
        $this->groupBefore($mpdf, $data);

        $this->groupLoop($mpdf, $data);

        $this->groupAfter($mpdf, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function reportAfter(Mpdf $mpdf, array $data): void
    {
        $html = $this->getReportAfterHtml($data);

        if (! empty($html)) {
            $this->writeHtml($mpdf, $html);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function groupLoop(Mpdf $mpdf, array $data): void
    {
        $this->subGroupBefore($mpdf, $data);

        if ($this->hasGroupItems()) {
            $groupItems = $this->getGroupItems();
            $totalGroupItems = count($groupItems);

            foreach ($groupItems as $groupIndex => $group) {
                $this->setCurrentGroup($group);
                $this->setCurrentGroupIndex($groupIndex);

                $this->subGroupLoop($mpdf, $data);

                if ($groupIndex < $totalGroupItems - 1) {
                    $mpdf->AddPage();
                }
            }
        } else {
            $this->pageContent($mpdf, $data);
        }

        $this->subGroupAfter($mpdf, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function subGroupLoop(Mpdf $mpdf, array $data): void
    {
        if ($this->hasSubGroupItems()) {
            $subGroupItems = $this->getSubGroupItems();
            $totalSubGroupItems = count($subGroupItems);

            foreach ($subGroupItems as $subGroupIndex => $subGroup) {
                $this->setCurrentSubGroup($subGroup);
                $this->setCurrentSubGroupIndex($subGroupIndex);

                $this->pageContent($mpdf, $data);

                if ($subGroupIndex < $totalSubGroupItems - 1) {
                    $mpdf->AddPage();
                }
            }
        } else {
            $this->pageContent($mpdf, $data);
        }
    }

    // -------------------------------------------------------------------------
    // Grouping Logic (Public API & Protected Hooks)
    // -------------------------------------------------------------------------

    public function hasGroupItems(): bool
    {
        return ($this->getGroupItems()?->count() ?? 0) > 0;
    }

    public function hasSubGroupItems(): bool
    {
        return ($this->getSubGroupItems()?->count() ?? 0) > 0;
    }

    protected function getGroupItems(): ?Collection
    {
        return null;
    }

    protected function getSubGroupItems(): ?Collection
    {
        return null;
    }

    // -------------------------------------------------------------------------
    // Column & Row Data
    // -------------------------------------------------------------------------

    public function getTableRows(): Collection
    {
        return $this
            ->getTableRowsData()
            ->values()
            ->map(function (Model|Collection|array|null $record, $index) {
                $this->setRecord($record ?? []);

                return collect($this->getColumnsData($this->getRowNumber($index)));
            });
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
        return $this->cachedColumns ??= array_reduce(
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

        return collect($this->selectedColumns)
            ->keys()
            ->map(fn (string $column) => [
                'width' => $columns[$column]->getSpanPercentage(),
                'text' => $columns[$column]->getLabel(),
            ]);
    }

    public function getColumnsData(int|string $sequenceNumber = ''): Collection
    {
        if (array_key_exists('__row__', $this->selectedColumns)) {
            $this->record['__row__'] = $sequenceNumber;
        }

        $columns = $this->getCachedColumns();

        return collect($this->selectedColumns)
            ->keys()
            ->map(fn (string $column) => [
                'width' => $columns[$column]->getSpanPercentage(),
                'text' => $columns[$column]->getFormattedState(),
                'align' => $columns[$column]->getAlign(),
                'style' => $columns[$column]->getStyle(),
            ]);
    }

    public function getSelectedColumns(): array
    {
        return array_values(array_intersect_key($this->getCachedColumns(), $this->selectedColumns));
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

    // -------------------------------------------------------------------------
    // HTML / View Hooks (To be overridden)
    // -------------------------------------------------------------------------

    /** @param  array<string, mixed>  $data */
    public function getStyles(array $data): string|Htmlable
    {
        return '';
    }

    /** @param  array<string, mixed>  $data */
    public function getHtmlHead(array $data): string|Htmlable
    {
        return '';
    }

    /** @param  array<string, mixed>  $data */
    public function getReportHeader(array $data): string|Htmlable
    {
        if ($data['default_header'] ?? true) {
            return View::make('fb-report::components.header', compact('data'))->render();
        }

        return '';
    }

    /** @param  array<string, mixed>  $data */
    public function getReportFooter(array $data): string|Htmlable
    {
        if ($data['default_footer'] ?? true) {
            return View::make('fb-report::components.footer', compact('data'))->render();
        }

        return '';
    }

    /** @param  array<string, mixed>  $data */
    public function getReportTitle(array $data): string|Htmlable
    {
        return '';
    }

    /** @param  array<string, mixed>  $data */
    public function getReportDescription(array $data): string|Htmlable
    {
        return '';
    }

    /** @param  array<string, mixed>  $data */
    public function getGroupBeforeHtml(array $data): string|Htmlable
    {
        return '';
    }

    /** @param  array<string, mixed>  $data */
    public function getGroupAfterHtml(array $data): string|Htmlable
    {
        return '';
    }

    /** @param  array<string, mixed>  $data */
    public function getSubGroupBeforeHtml(array $data): string|Htmlable
    {
        return '';
    }

    /** @param  array<string, mixed>  $data */
    public function getSubGroupAfterHtml(array $data): string|Htmlable
    {
        return '';
    }

    /** @param  array<string, mixed>  $data */
    public function getBeforeHtml(array $data): string|Htmlable
    {
        return '';
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  Collection<int, mixed>  $titles
     * @param  Collection<int, mixed>  $rows
     */
    public function getMainHtml(array $data, Collection $titles, Collection $rows): string|Htmlable
    {
        return View::make('fb-report::components.table', compact('data', 'titles', 'rows'))->render();
    }

    /** @param  array<string, mixed>  $data */
    public function getAfterHtml(array $data): string|Htmlable
    {
        return '';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function getReportAfterHtml(array $data): string|Htmlable
    {
        return '';
    }

    private function writeHtml(Mpdf $mpdf, string|Htmlable $html): void
    {
        $mpdf->WriteHTML($html instanceof Htmlable ? $html->toHtml() : $html);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function groupBefore(Mpdf $mpdf, array $data): void
    {
        $html = $this->getGroupBeforeHtml($data);

        if (! empty($html)) {
            $this->writeHtml($mpdf, $html);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function groupAfter(Mpdf $mpdf, array $data): void
    {
        $html = $this->getGroupAfterHtml($data);

        if (! empty($html)) {
            $this->writeHtml($mpdf, $html);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function subGroupBefore(Mpdf $mpdf, array $data): void
    {
        $html = $this->getSubGroupBeforeHtml($data);

        if (! empty($html)) {
            $this->writeHtml($mpdf, $html);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function subGroupAfter(Mpdf $mpdf, array $data): void
    {
        $html = $this->getSubGroupAfterHtml($data);

        if (! empty($html)) {
            $this->writeHtml($mpdf, $html);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function pageBefore(Mpdf $mpdf, array $data): void
    {
        $html = $this->getBeforeHtml($data);

        if (! empty($html)) {
            $this->writeHtml($mpdf, $html);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function pageAfter(Mpdf $mpdf, array $data): void
    {
        $html = $this->getAfterHtml($data);

        if (! empty($html)) {
            $this->writeHtml($mpdf, $html);
        }
    }

    // -------------------------------------------------------------------------
    // PDF Hooks
    // -------------------------------------------------------------------------

    public function mpdfBeforHtml(LaravelMpdf $laravelMpdf): void
    {
        //
    }

    public function mpdfAfterHtml(LaravelMpdf $laravelMpdf): void
    {
        //
    }

    // -------------------------------------------------------------------------
    // Internal State Management (Getters/Setters)
    // -------------------------------------------------------------------------

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

    public function setModel(?string $model): void
    {
        static::$model = $model;
    }

    public function setCurrentGroup(array|Collection|Model|null $item): void
    {
        $this->currentGroup = $item;
    }

    public function getCurrentGroup(): array|Collection|Model|null
    {
        return $this->currentGroup;
    }

    public function setCurrentGroupIndex(int|string|null $index): void
    {
        $this->currentGroupIndex = $index;
    }

    public function setCurrentSubGroup(array|Collection|Model|null $item): void
    {
        $this->currentSubGroup = $item;
    }

    public function getCurrentSubGroup(): array|Collection|Model|null
    {
        return $this->currentSubGroup;
    }

    public function setCurrentSubGroupIndex(int|string|null $index): void
    {
        $this->currentSubGroupIndex = $index;
    }

    // -------------------------------------------------------------------------
    // Utility Getters
    // -------------------------------------------------------------------------

    public function getOptions(): array
    {
        return $this->options ?? [];
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

    public function getPageTitle(): string|Htmlable
    {
        return '';
    }

    public function getReportPageName(): ?string
    {
        return $this->reportPageName;
    }

    // -------------------------------------------------------------------------
    // Private Render Helpers
    // -------------------------------------------------------------------------
}
