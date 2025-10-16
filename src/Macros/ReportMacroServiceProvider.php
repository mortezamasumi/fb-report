<?php

namespace Mortezamasumi\FbReport\Macros;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Mortezamasumi\FbEssentials\Facades\FbPersian;
use Mortezamasumi\FbReport\Reports\ReportColumn;
use Closure;

/**
 * Interface declaring Table macros for IDE support
 *
 * @method static Column jDate(string|Closure|null $Tableat, ?string $timezone) jDate apply
 * @method static Column jDateTime(string|Closure|null $Tableat, ?string $timezone, bool|Closure $onlyDate) jDateTime apply
 * @method static Column localeDigit(?string $forceLocale) current locale apply
 */
interface ReportMacrosInterface {}

class ReportMacroServiceProvider extends ServiceProvider
{
    public function boot()
    {
        ReportColumn::macro('jDate', function (string|Closure|null $format = null, ?string $timezone = null, ?string $forceLocale = null): ReportColumn {
            /** @var ReportColumn $this */
            $this->jDateTime($format, $timezone, $forceLocale, true);

            return $this;
        });

        ReportColumn::macro('jDateTime', function (string|Closure|null $format = null, ?string $timezone = null, ?string $forceLocale = null, bool|Closure $onlyDate = false): ReportColumn {
            /** @var ReportColumn $this */
            $this->formatStateUsing(static function (ReportColumn $column, Model $record, $state) use ($format, $timezone, $forceLocale, $onlyDate): ?string {
                if (blank($state)) {
                    return null;
                }

                $format = $column->evaluate($format, ['record' => $record, 'state' => $state]);
                $onlyDate = $column->evaluate($onlyDate, ['record' => $record, 'state' => $state]);
                $format ??= ($onlyDate ? __('fb-essentials::fb-essentials.date_format.simple') : __('fb-essentials::fb-essentials.date_format.time_simple'));

                return FbPersian::jDateTime($format, $state, $timezone, $forceLocale);
            });

            return $this;
        });

        ReportColumn::macro('localeDigit', function (?string $forceLocale = null): ReportColumn {
            /** @var ReportColumn $this */
            $this->formatStateUsing(static fn (mixed $state) => in_array(gettype($state), ['integer', 'double', 'string']) ? FbPersian::digit($state, $forceLocale) : $state);

            return $this;
        });

        ReportColumn::mixin(new class implements ReportMacrosInterface {});
    }
}
