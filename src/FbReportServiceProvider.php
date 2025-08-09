<?php

namespace Mortezamasumi\FbReport;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Livewire\Features\SupportTesting\Testable;
use Mortezamasumi\FbReport\Testing\TestsFbReport;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FbReportServiceProvider extends PackageServiceProvider
{
    public static string $name = 'fb-report';
    public static string $viewNamespace = 'fb-report';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasTranslations()
            ->hasViews(static::$viewNamespace);
    }

    public function packageBooted(): void
    {
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        Testable::mixin(new TestsFbReport);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'mortezamasumi/fb-report';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            Css::make('fb-report-styles', __DIR__.'/../resources/dist/css/index.css'),
        ];
    }
}
