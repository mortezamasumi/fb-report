<?php

namespace Mortezamasumi\FbReport\Reports;

use Filament\Support\Components\Component;
use Filament\Support\Concerns\CanAggregateRelatedModels;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Arr;
use Illuminate\Support\Number;
use Mortezamasumi\FbEssentials\Facades\FbPersian;
use Mortezamasumi\FbReport\Concerns\CanFormatState;
use Mortezamasumi\FbReport\Concerns\HasCellState;
use Closure;

class ReportColumn1111 extends Component
{
    use CanAggregateRelatedModels;
    use CanFormatState;
    use HasCellState;

    protected string $name;
    protected string|Closure|null $label = null;
    protected ?Reporter $reporter = null;
    protected bool|Closure $isEnabledByDefault = true;
    protected string $evaluationIdentifier = 'column';
    protected int $span = 1;
    protected ?string $align = null;
    protected ?string $style = null;
    protected $shouldTranslateLabel = false;

    final public function __construct(string $name)
    {
        $this->name($name);
    }

    public static function make(string $name): static
    {
        $static = app(static::class, ['name' => $name]);
        $static->configure();

        return $static;
    }

    public function name(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string|Htmlable|null
    {
        $label = $this->evaluate($this->label) ?? (string) str($this->getName())
            ->beforeLast('.')
            ->afterLast('.')
            ->kebab()
            ->replace(['-', '_'], ' ')
            ->ucfirst();

        return (is_string($label) && $this->shouldTranslateLabel)
            ? __($label)
            : $label;
    }

    public function label(string|Closure|null $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function translateLabel(bool $shouldTranslateLabel = true): static
    {
        $this->shouldTranslateLabel = $shouldTranslateLabel;

        return $this;
    }

    public function span(int|Closure|null $span): static
    {
        $this->span = $span;

        return $this;
    }

    public function align(string|Closure $align): static
    {
        $this->align = $align;

        return $this;
    }

    public function style(string|Closure $style): static
    {
        $this->style = $style;

        return $this;
    }

    public function getStyle(): ?string
    {
        return $this->evaluate($this->style) ?? '';
    }

    public function getAlign(): ?string
    {
        $align = $this->evaluate($this->align) ?? 'center';

        if (in_array(App::getLocale(), ['fa', 'ar', 'ur', 'he'])) {
            if ($align === 'start') {
                $align = 'right';
            }
            if ($align === 'end') {
                $align = 'left';
            }
        } else {
            if ($align === 'start') {
                $align = 'left';
            }
            if ($align === 'end') {
                $align = 'right';
            }
        }

        return $align;
    }

    public function getSpan(): int
    {
        return $this->evaluate($this->span) ?? 1;
    }

    public function getSpanPercentage(): string
    {
        if ($this->getReporter()->getColumnsSpan() <= 0) {
            return '';
        }

        return Number::format(number: 100 * ($this->getSpan() / $this->getReporter()->getColumnsSpan()), precision: 2);
    }

    public function reporter(?Reporter $reporter): static
    {
        $this->reporter = $reporter;

        return $this;
    }

    public function enabledByDefault(bool|Closure $condition = true): static
    {
        $this->isEnabledByDefault = $condition;

        return $this;
    }

    public function isEnabledByDefault(): bool
    {
        return (bool) $this->evaluate($this->isEnabledByDefault);
    }

    public function getReporter(): ?Reporter
    {
        return $this->reporter;
    }

    public function getRecord(): mixed
    {
        return $this->getReporter()?->getRecord();
    }

    public function applyRelationshipAggregates(EloquentBuilder $query): EloquentBuilder
    {
        return $query->when(
            filled([$this->getRelationshipToAvg(), $this->getColumnToAvg()]),
            fn ($query) => $query->withAvg($this->getRelationshipToAvg(), $this->getColumnToAvg())
        )->when(
            filled($this->getRelationshipsToCount()),
            fn ($query) => $query->withCount(Arr::wrap($this->getRelationshipsToCount()))
        )->when(
            filled($this->getRelationshipsToExistenceCheck()),
            fn ($query) => $query->withExists(Arr::wrap($this->getRelationshipsToExistenceCheck()))
        )->when(
            filled([$this->getRelationshipToMax(), $this->getColumnToMax()]),
            fn ($query) => $query->withMax($this->getRelationshipToMax(), $this->getColumnToMax())
        )->when(
            filled([$this->getRelationshipToMin(), $this->getColumnToMin()]),
            fn ($query) => $query->withMin($this->getRelationshipToMin(), $this->getColumnToMin())
        )->when(
            filled([$this->getRelationshipToSum(), $this->getColumnToSum()]),
            fn ($query) => $query->withSum($this->getRelationshipToSum(), $this->getColumnToSum())
        );
    }

    public function applyEagerLoading(EloquentBuilder $query): EloquentBuilder
    {
        if (! $this->hasRelationship($query->getModel())) {
            return $query;
        }

        $relationshipName = $this->getRelationshipName();

        if (array_key_exists($relationshipName, $query->getEagerLoads())) {
            return $query;
        }

        return $query->with([$relationshipName]);
    }

    protected function resolveDefaultClosureDependencyForEvaluationByName(string $parameterName): array
    {
        return match ($parameterName) {
            'reporter' => [$this->getReporter()],
            'options' => [$this->getReporter()->getOptions()],
            'record' => [$this->getRecord()],
            default => parent::resolveDefaultClosureDependencyForEvaluationByName($parameterName),
        };
    }

    protected function resolveDefaultClosureDependencyForEvaluationByType(string $parameterType): array
    {
        $record = $this->getRecord();

        return match ($parameterType) {
            Reporter::class => [$this->getReporter()],
            Model::class, $record ? $record::class : null => [$record],
            default => parent::resolveDefaultClosureDependencyForEvaluationByType($parameterType),
        };
    }

    public function jDate(?string $format = null, ?string $timezone = null, ?string $forceLocale = null): static
    {
        $this->jDateTime($format, $timezone, $forceLocale, true);

        return $this;
    }

    public function jdateTime(string|Closure|null $format = null, ?string $timezone = null, ?string $forceLocale = null, bool|Closure $onlyDate = false): static
    {
        $this->formatStateUsing(
            static function ($column, mixed $record, $state) use ($format, $timezone, $forceLocale, $onlyDate): ?string {
                if (blank($state)) {
                    return null;
                }

                $format = $column->evaluate($format, ['record' => $record, 'state' => $state]);
                $onlyDate = $column->evaluate($onlyDate, ['record' => $record, 'state' => $state]);
                $format ??= ($onlyDate ? __f_date() : __f_datetime());

                return FbPersian::jDateTime($format, $state, $timezone, $forceLocale);
            }
        );

        return $this;
    }

    public function localeDigit(?string $forceLocale = null): static
    {
        $this->formatStateUsing(
            static fn (mixed $state) => in_array(gettype($state), ['integer', 'double', 'string'])
                ? FbPersian::digit($state, $forceLocale)
                : $state
        );

        return $this;
    }
}
