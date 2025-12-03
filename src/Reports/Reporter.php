<?php

namespace Mortezamasumi\FbReport\Reports;

use Filament\Support\Concerns\EvaluatesClosures;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Collection;
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

    public function pageContent($mpdf, $data): void
    {
        $titles = $this->getColumnsTitle();
        $rows = $this->getTableRows();

        $this->pageBefore($mpdf, $data);
        $mpdf->WriteHTML($this->getMainHtml($data, $titles, $rows));
        $this->pageAfter($mpdf, $data);
    }

    public function makeContent($mpdf, $data): void
    {
        $this->groupBefore($mpdf, $data);

        $this->groupLoop($mpdf, $data);

        $this->groupAfter($mpdf, $data);
    }

    private function groupLoop($mpdf, $data): void
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

    private function subGroupLoop($mpdf, $data): void
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

    /* */
    /* */
    /* */
    /* */
    /* */
    /* */
    /* */

    // public function getReportBody($data): string|Htmlable
    // {
    //     // $this->setRecord(collect([]));

    //     $html = $this->getGroupBeforeHtml($data);

    //     // if (!$this->hasGroupItems()) {
    //     //     $html .= $this->getReportContent($data);
    //     // } else {
    //     $html .= $this->renderGroupLoop($data);
    //     // }

    //     return $html . $this->getGroupAfterHtml($data);
    // }

    // public function getReportContent($data): string|Htmlable
    // {
    //     $titles = $this->getColumnsTitle();
    //     $rows = $this->getTableRows();

    //     $before = $this->getBeforeHtml($data);
    //     $main = $this->getMainHtml($data, $titles, $rows);
    //     $after = $this->getAfterHtml($data);

    //     return $before . $main . $after;
    //     // return $main;
    // }

    // private function renderGroupLoop($data): string
    // {
    //     $html = $this->getSubGroupBeforeHtml($data);

    //     if ($this->hasGroupItems()) {
    //         $groupItems = $this->getGroupItems();
    //         $totalGroupItems = count($groupItems);

    //         foreach ($groupItems as $groupIndex => $group) {
    //             $this->setCurrentGroup($group);
    //             $this->setCurrentGroupIndex($groupIndex);

    //             $html .= $this->renderSubGroupLoop($data);

    //             if ($groupIndex < $totalGroupItems - 1) {
    //                 $html .= '<pagebreak />';
    //             }
    //         }
    //     } else {
    //         $html .= $this->getReportContent($data);
    //     }

    //     return $html . $this->getSubGroupAfterHtml($data);
    // }

    // private function renderSubGroupLoop($data): string
    // {
    //     $html = '';

    //     if ($this->hasSubGroupItems()) {
    //         $subGroupItems = $this->getSubGroupItems();
    //         $totalSubGroupItems = count($subGroupItems);

    //         foreach ($subGroupItems as $subGroupIndex => $subGroup) {
    //             $this->setCurrentSubGroup($subGroup);
    //             $this->setCurrentSubGroupIndex($subGroupIndex);

    //             $html .= $this->getReportContent($data);

    //             if ($subGroupIndex < $totalSubGroupItems - 1) {
    //                 $html .= '<pagebreak />';
    //             }
    //         }
    //     } else {
    //         $html .= $this->getReportContent($data);
    //     }

    //     return $html;
    // }

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
        // <-- FIX: Caching now works correctly
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

        // <-- SUGGESTION: Use collection pipeline
        return collect($this->selectedColumns)
            ->keys()
            ->map(fn(string $column) => [
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

        // <-- SUGGESTION: Use collection pipeline
        return collect($this->selectedColumns)
            ->keys()
            ->map(fn(string $column) => [
                'width' => $columns[$column]->getSpanPercentage(),
                'text' => $columns[$column]->getFormattedState(),
                'align' => $columns[$column]->getAlign(),
                'style' => $columns[$column]->getStyle(),
            ]);
    }

    public function getSelectedColumns(): array
    {
        return array_values(array_intersect_key($this->getCachedColumns(), $this->selectedColumns));

        // $columns = $this->getCachedColumns();
        // $data = [];
        // foreach (array_keys($this->selectedColumns) as $column) {
        //     $data[] = $columns[$column];
        // }

        // return $data;
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

    public function getStyles($data): string|Htmlable
    {
        return '';
    }

    public function getHtmlHead($data): string|Htmlable
    {
        return '';
    }

    public function getReportHeader($data): string|Htmlable
    {
        if ($data['default_header'] ?? true) {
            return View::make('fb-report::components.header', compact('data'))->render();
        }
        return '';
    }

    public function getReportFooter($data): string|Htmlable
    {
        if ($data['default_footer'] ?? true) {
            return View::make('fb-report::components.footer', compact('data'))->render();
        }
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

    public function getGroupBeforeHtml($data): string|Htmlable
    {
        return '';
    }

    public function getGroupAfterHtml($data): string|Htmlable
    {
        return '';
    }

    public function getSubGroupBeforeHtml($data): string|Htmlable
    {
        return '';
    }

    public function getSubGroupAfterHtml($data): string|Htmlable
    {
        return '';
    }

    public function getBeforeHtml($data): string|Htmlable
    {
        return '';
    }

    public function getMainHtml($data, $titles, $rows): string|Htmlable
    {
        return View::make('fb-report::components.table', compact('data', 'titles', 'rows'))->render();
    }

    public function getAfterHtml($data): string|Htmlable
    {
        return '';
    }

    public function groupBefore($mpdf, $data): void
    {
        $html = $this->getGroupBeforeHtml($data);

        if (!empty($html)) {
            $mpdf->WriteHTML($html);
        }
    }

    public function groupAfter($mpdf, $data): void
    {
        $html = $this->getGroupAfterHtml($data);

        if (!empty($html)) {
            $mpdf->WriteHTML($html);
        }
    }

    public function subGroupBefore($mpdf, $data): void
    {
        $html = $this->getSubGroupBeforeHtml($data);

        if (!empty($html)) {
            $mpdf->WriteHTML($html);
        }
    }

    public function subGroupAfter($mpdf, $data): void
    {
        $html = $this->getSubGroupAfterHtml($data);

        if (!empty($html)) {
            $mpdf->WriteHTML($html);
        }
    }

    public function pageBefore($mpdf, $data): void
    {
        $html = $this->getBeforeHtml($data);

        if (!empty($html)) {
            $mpdf->WriteHTML($html);
        }
    }

    public function pageAfter($mpdf, $data): void
    {
        $html = $this->getAfterHtml($data);

        if (!empty($html)) {
            $mpdf->WriteHTML($html);
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
