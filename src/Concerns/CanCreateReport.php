<?php

namespace Mortezamasumi\FbReport\Concerns;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Flex;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;
use Mortezamasumi\FbReport\Actions\ReportAction;
use Mortezamasumi\FbReport\Actions\ReportBulkAction;
use Mortezamasumi\FbReport\Reports\ReportColumn;
use Mortezamasumi\FbReport\Reports\Reporter;
use Closure;
use ReflectionClass;

trait CanCreateReport
{
    // -------------------------------------------------------------------------
    // Properties
    // -------------------------------------------------------------------------

    /** @var class-string<Reporter> */
    protected string $reporter;
    protected bool|Closure $selectableColumns = true;
    protected bool|Closure $hasForceUseReporterModel = false;  // <-- FIX: Typo
    protected bool|Closure $hasRequiredConfirmation = false;
    /** @var array<string, mixed> | Closure */
    protected array|Closure $options = [];
    protected ?Closure $modifyQueryUsing = null;
    protected Closure|string|null $auxModel = null;
    protected Closure|Model|Collection|array|null $auxRecord = null;

    // -------------------------------------------------------------------------
    // Main Setup
    // -------------------------------------------------------------------------

    protected function setUp(): void
    {
        parent::setUp();

        $this->configureModal();
        $this->configureForm();
        $this->configureAction();
    }

    public static function getDefaultName(): ?string
    {
        return 'report';
    }

    // -------------------------------------------------------------------------
    // Configuration Methods (Fluent API)
    // -------------------------------------------------------------------------

    /**
     * @param  class-string<Reporter>  $reporter
     */
    public function reporter(string $reporter): static
    {
        $this->reporter = $reporter;

        return $this;
    }

    public function options(array|Closure $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function modifyQueryUsing(?Closure $callback): static
    {
        $this->modifyQueryUsing = $callback;

        return $this;
    }

    public function selectableColumns(bool|Closure $condition = true): static
    {
        $this->selectableColumns = $condition;

        return $this;
    }

    public function forceUseReporterModel(bool|Closure $condition = true): static
    {
        $this->hasForceUseReporterModel = $condition;  // <-- FIX: Typo

        return $this;
    }

    public function useRecord(Closure|Model|Collection|array|null $record): static
    {
        $this->auxRecord = $record;

        return $this;
    }

    public function useModel(Closure|string|null $model): static
    {
        $this->auxModel = $model;

        return $this;
    }

    // -------------------------------------------------------------------------
    // Internal Setup Helpers
    // -------------------------------------------------------------------------

    protected function configureModal(): void
    {
        $this->label(
            fn (ReportAction|ReportBulkAction $action, Component $livewire): string
                => __('fb-report::fb-report.label', ['label' => $action->getActionLabel($livewire)])
        );

        $this->modalHeading(
            fn (ReportAction|ReportBulkAction $action, Component $livewire): string
                => __('fb-report::fb-report.heading', ['heading' => $action->getActionHeading($livewire)])
        );

        $this->modalSubmitActionLabel(__('fb-report::fb-report.action'));
        $this->groupedIcon('heroicon-o-printer');
        $this->color('gray');
        $this->modalWidth('xl');

        $this->modalHidden(function (ReportAction|ReportBulkAction $action) {
            if ($action->isConfirmationRequired()) {
                return false;
            }

            if (count($action->getReporter()::getOptionsFormComponents())) {
                return false;
            }

            return ! $action->hasSelectableColumns();
        });
    }

    protected function configureForm(): void
    {
        $this->form(fn (ReportAction|ReportBulkAction $action): array
            => $this->getReportFormSchema($action));
    }

    protected function configureAction(): void
    {
        $this->action(
            fn (ReportAction|ReportBulkAction $action, array $data, Component $livewire)
                => $this->handleReportAction($action, $data, $livewire)
        );
    }

    // -------------------------------------------------------------------------
    // Form Schema Builders
    // -------------------------------------------------------------------------

    protected function getReportFormSchema(ReportAction|ReportBulkAction $action): array
    {
        return [
            $this->getSelectableColumnsFieldset($action),
            ...$action->getReporter()::getOptionsFormComponents(),
        ];
    }

    protected function getSelectableColumnsFieldset(ReportAction|ReportBulkAction $action): Fieldset
    {
        return Fieldset::make(__('fb-report::fb-report.columns'))
            ->columns(1)
            ->inlineLabel()
            ->schema(fn () => array_map(
                fn (ReportColumn $column): Flex => Flex::make([
                    Checkbox::make('isEnabled')
                        ->hiddenLabel()
                        ->default(fn () => $column->isEnabledByDefault())
                        ->grow(false),
                    TextInput::make('label')
                        ->hiddenLabel()
                        ->default($column->getLabel())
                        ->readOnly(),
                ])
                    ->verticallyAlignCenter()
                    ->statePath($column->getName()),
                $action->getReporter()::getColumns(),
            ))
            ->statePath('selectedColumns')
            ->visible($this->hasSelectableColumns());
    }

    // -------------------------------------------------------------------------
    // Action Handling Logic
    // -------------------------------------------------------------------------

    protected function handleReportAction(ReportAction|ReportBulkAction $action, array $data, Component $livewire): void
    {
        $reporterClass = $action->getReporter();

        $options = array_merge(
            $action->getOptions(),
            Arr::except($data, ['selectedColumns']),
        );

        $selectedColumns = $this->parseSelectedColumns(
            $reporterClass,
            $data,
            $action->hasSelectableColumns()
        );

        app($reporterClass, [
            'records' => $action->getActionRecords($livewire, $action, $reporterClass),
            'returnUrl' => $this->getReturnUrl($livewire),
            'selectedColumns' => $selectedColumns,
            'options' => $options,
            'reportPageName' => $action->getLabel(),
        ]);
    }

    private function parseSelectedColumns(string $reporter, array $data, bool $hasSelectableColumns): array
    {
        // 1. Get all columns as a default
        $allColumns = collect($reporter::getColumns())
            ->mapWithKeys(fn (ReportColumn $column): array => [$column->getName() => $column->getLabel()])
            ->all();

        if (! $hasSelectableColumns) {
            return $allColumns;
        }

        // 2. Filter columns based on form data
        $enabledColumns = collect($data['selectedColumns'] ?? [])
            ->dot()
            ->reduce(
                fn (Collection $carry, mixed $value, string $key): Collection => $carry->mergeRecursive([
                    Str::beforeLast($key, '.') => [Str::afterLast($key, '.') => $value],
                ]),
                collect()
            )
            ->filter(fn (array $column): bool => $column['isEnabled'] ?? false)
            ->mapWithKeys(fn (array $column, string $columnName): array => [$columnName => $column['label']])
            ->all();

        // 3. Intersect default columns with enabled ones to maintain order and validity
        return array_merge(
            array_flip(
                array_intersect(
                    array_keys($allColumns),
                    array_keys($enabledColumns)
                )
            ),
            $enabledColumns
        );
    }

    protected function getReturnUrl(Component $livewire): string
    {
        return method_exists($livewire, 'getUrl')
            ? $livewire->getUrl()
            : Filament::getUrl();
    }

    // -------------------------------------------------------------------------
    // Data Retrieval Logic
    // -------------------------------------------------------------------------

    public function getActionRecords(Component $livewire, Action $action, string $reporter): Collection
    {
        // 1. Priority: Bulk action selected records
        if ($action->canAccessSelectedRecords()) {
            return $action->getSelectedRecords();
        }

        // 2. Priority: Manually provided record(s)
        if ($this->hasAuxRecord()) {
            return collect([$this->getAuxRecord()]);
        }

        // 3. Priority: Manually provided model
        if ($this->hasAuxModel()) {
            $query = $this->getAuxModel()::query();

            return $this->applyQueryModifications($query, $reporter)->get();
        }

        // 4. Context: On a ListRecords page
        if ($livewire instanceof ListRecords) {
            // 4a. This is a single record action (e.g., in table row)
            if ($action->getRecord()) {
                return collect([$action->getRecord()]);
            }

            // 4b. This is a header action (report on all)
            $query = null;
            $reflection = new ReflectionClass($reporter);
            $method = $reflection->getMethod('getModel');

            // Check if getModel() was *overridden* in the child Reporter class.
            // If yes, use that model. If no, use the table's query.
            if ($method->getDeclaringClass()->getName() === $reporter || $this->hasForceUseReporterModel()) {
                $query = $reporter::getModel()::query();
            } else {
                $query = $livewire->getTableQueryForExport();
            }

            return $this->applyQueryModifications($query, $reporter)->get();
        }

        // 5. Context: On an EditRecord page
        if ($livewire instanceof EditRecord) {
            return collect([$action->getRecord()]);
        }

        // 6. Fallback: Use the reporter's default model, if any
        if ($reporter::getModel()) {
            $query = $reporter::getModel()::query();

            return $this->applyQueryModifications($query, $reporter)->get();
        }

        // 7. Give up
        return collect([]);
    }

    /**
     * Helper to apply both the reporter's modifyQuery and the action's modifyQuery.
     */
    private function applyQueryModifications(Builder $query, string $reporter): Builder
    {
        $query = $reporter::modifyQuery($query);

        if ($this->modifyQueryUsing) {
            $query = $this->evaluate($this->modifyQueryUsing, ['query' => $query]) ?? $query;
        }

        return $query;
    }

    // -------------------------------------------------------------------------
    // Internal Getters & Checkers
    // -------------------------------------------------------------------------

    /**
     * @return class-string<Reporter>
     */
    public function getReporter(): string
    {
        return $this->reporter;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->evaluate($this->options);
    }

    public function getAuxRecord(): Model|Collection|array|null
    {
        return $this->evaluate($this->auxRecord);
    }

    public function hasAuxRecord(): bool
    {
        return filled($this->auxRecord);
    }

    public function getAuxModel(): string
    {
        return $this->evaluate($this->auxModel);
    }

    public function hasAuxModel(): bool
    {
        return filled($this->auxModel);
    }

    public function hasSelectableColumns(): bool
    {
        /** @var Reporter $reporter */
        $reporter = $this->getReporter();

        /** @disregard */
        return (bool) $this->evaluate($this->selectableColumns) && $reporter::$selectableColumns;
    }

    public function hasForceUseReporterModel(): bool
    {
        return (bool) $this->evaluate($this->hasForceUseReporterModel);  // <-- FIX: Typo
    }
}
