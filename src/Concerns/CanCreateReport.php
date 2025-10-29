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
    /** @var class-string<Reporter> */
    protected string $reporter;
    protected bool|Closure $selectableColumns = true;
    protected bool|Closure $hasForceUseReporterModel = false;
    protected bool|Closure $hasRequiredConfirmation = false;
    /** @var array<string, mixed> | Closure */
    protected array|Closure $options = [];
    protected ?Closure $modifyQueryUsing = null;
    protected Closure|string|null $auxModel = null;
    protected Closure|Model|Collection|array|null $auxRecord = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(
            function (
                ReportAction|ReportBulkAction $action,
                Component $livewire
            ): string {
                return __('fb-report::fb-report.label', ['label' => $action->getActionLabel($livewire)]);
            }
        );

        $this->modalHeading(
            function (
                ReportAction|ReportBulkAction $action,
                Component $livewire
            ): string {
                return __('fb-report::fb-report.heading', ['heading' => $action->getActionHeading($livewire)]);
            }
        );

        $this->modalSubmitActionLabel(__('fb-report::fb-report.action'));

        $this->groupedIcon('heroicon-o-printer');

        $this->form(fn (ReportAction|ReportBulkAction $action): array => [
            Fieldset::make(__('fb-report::fb-report.columns'))
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
                ->visible($this->hasSelectableColumns()),
            ...$action->getReporter()::getOptionsFormComponents(),
        ]);

        $this->action(
            function (
                ReportAction|ReportBulkAction $action,
                array $data,
                Component $livewire
            ) {
                $options = array_merge(
                    $action->getOptions(),
                    Arr::except($data, ['selectedColumns']),
                );

                $reporter = $action->getReporter();

                $selectedColumns = collect($reporter::getColumns())
                    ->mapWithKeys(
                        fn (ReportColumn $column): array => [$column->getName() => $column->getLabel()]
                    )
                    ->all();

                if ($action->hasSelectableColumns()) {
                    $temp = collect($data['selectedColumns'])
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

                    $selectedColumns = array_merge(
                        array_flip(
                            array_intersect(
                                array_keys($selectedColumns),
                                array_keys($temp)
                            )
                        ),
                        $temp
                    );
                }

                $reporter = app($reporter, [
                    'records' => $action->getActionRecords($livewire, $action, $reporter),
                    'returnUrl' => (
                        method_exists($livewire, 'getUrl')
                            ? $livewire->getUrl()
                            : Filament::getUrl()
                    ),
                    'selectedColumns' => $selectedColumns,
                    'options' => $options,
                    'reportPageName' => $action->getLabel(),
                ]);
            }
        );

        $this->color('gray');

        $this->modalWidth('xl');

        $this->modalHidden(function (ReportAction|ReportBulkAction $action) {
            if ($action->isConfirmationRequired()) {
                return false;
            };

            if (count($action->getReporter()::getOptionsFormComponents())) {
                return false;
            }

            return ! $action->hasSelectableColumns();
        });
    }

    public function useRecord(Closure|Model|Collection|array|null $record): static
    {
        $this->auxRecord = $record;

        return $this;
    }

    public function getAuxRecord(): Model|Collection|array|null
    {
        return $this->evaluate($this->auxRecord);
    }

    public function hasAuxRecord(): bool
    {
        return filled($this->auxRecord);
    }

    public function useModel(Closure|string|null $model): static
    {
        $this->auxModel = $model;

        return $this;
    }

    public function getAuxModel(): string
    {
        return $this->evaluate($this->auxModel);
    }

    public function hasAuxModel(): bool
    {
        return filled($this->auxModel);
    }

    public function getActionRecords(Component $livewire, Action $action, string $reporter): Collection
    {
        if ($action->canAccessSelectedRecords()) {
            return $action->getSelectedRecords();
        }

        if ($this->hasAuxRecord()) {
            return collect([$this->getAuxRecord()]);
        }

        if ($this->hasAuxModel()) {
            $query = $this->getAuxModel()::query();

            $query = $reporter::modifyQuery($query);

            if ($this->modifyQueryUsing) {
                $query = $this->evaluate($this->modifyQueryUsing, ['query' => $query]) ?? $query;
            }

            return $query->get();
        }

        if ($livewire instanceof ListRecords) {
            if ($action->getRecord()) {
                return collect([$action->getRecord()]);
            }

            $reflection = new ReflectionClass($reporter);
            $method = $reflection->getMethod('getModel');

            if ($method->getDeclaringClass()->getName() === $reporter) {
                $query = $reporter::getModel()::query();
            } else {
                $query = $livewire->getTableQueryForExport();
            }

            $query = $reporter::modifyQuery($query);

            if ($this->modifyQueryUsing) {
                $query = $this->evaluate($this->modifyQueryUsing, ['query' => $query]) ?? $query;
            }

            return $query->get();
        }

        if ($livewire instanceof EditRecord) {
            return collect([$action->getRecord()]);
        }

        if ($reporter::getModel()) {
            $query = $reporter::getModel()::query();

            $query = $reporter::modifyQuery($query);

            if ($this->modifyQueryUsing) {
                $query = $this->evaluate($this->modifyQueryUsing, ['query' => $query]) ?? $query;
            }

            return $query->get();
        }

        return collect([]);
    }

    public static function getDefaultName(): ?string
    {
        return 'report';
    }

    /**
     * @param  class-string<Reporter>  $reporter
     */
    public function reporter(string $reporter): static
    {
        $this->reporter = $reporter;

        return $this;
    }

    /**
     * @return class-string<Reporter>
     */
    public function getReporter(): string
    {
        return $this->reporter;
    }

    public function options(array|Closure $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->evaluate($this->options);
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

    public function hasSelectableColumns(): bool
    {
        /** @var Reporter $reporter */
        $reporter = $this->getReporter();

        /** @disregard */
        return (bool) $this->evaluate($this->selectableColumns) && $reporter::$selectableColumns;
    }

    public function forceUseReporterModel(bool|Closure $condition = true): static
    {
        $this->hasforceUseReporterModel = $condition;

        return $this;
    }

    public function hasForceUseReporterModel(): bool
    {
        return (bool) $this->evaluate($this->hasForceUseReporterModel);
    }
}
