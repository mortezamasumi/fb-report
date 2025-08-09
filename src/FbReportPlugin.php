<?php

namespace Mortezamasumi\FbReport;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Mortezamasumi\FbReport\Reports\ReportPage;

class FbReportPlugin implements Plugin
{
    public function getId(): string
    {
        return 'fb-report';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->pages([
                ReportPage::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
