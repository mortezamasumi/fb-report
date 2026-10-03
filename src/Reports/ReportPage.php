<?php

namespace Mortezamasumi\FbReport\Reports;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Livewire\Attributes\Locked;
use Throwable;

class ReportPage extends Page
{
    protected string $view = 'fb-report::filament.pages.report';

    protected static string|array $routeMiddleware = 'signed';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'report';

    protected const RTL_LANGUAGES = ['fa', 'ar', 'ur', 'he'];

    #[Locked]
    public ?string $base64Pdf = null;

    #[Locked]
    public ?string $reporterKey = null;

    #[Locked]
    public ?string $reportDataKey = null;

    #[Locked]
    public ?string $reportConfigKey = null;

    #[Locked]
    public ?string $returnUrl = null;

    #[Locked]
    public bool $showLoadingScreen = false;

    #[Locked]
    public string $generationState = 'pending';

    protected ?Reporter $reporter = null;

    /** @var array<string, mixed> */
    protected array $reportData = [];

    /** @var array<string, mixed> */
    protected array $reportConfig = [];

    protected string $lang;

    protected string $dir;

    public function mount(): void
    {
        $this->returnUrl = request()->get('returnUrl');
        $this->reporterKey = request()->get('reporter');
        $this->reportDataKey = request()->get('reportData');
        $this->reportConfigKey = request()->get('reportConfig');
        $this->showLoadingScreen = request()->boolean('showLoadingScreen', true);

        if (! $this->initializeReport()) {
            redirect($this->returnUrl);

            return;
        }

        if (! $this->showLoadingScreen) {
            $this->generateReportContent();
            $this->generationState = 'ready';
        }
    }

    public function hydrate(): void
    {
        if (! $this->initializeReport()) {
            abort(404);
        }
    }

    public function generateReport(): void
    {
        if (! $this->showLoadingScreen || $this->generationState === 'ready') {
            return;
        }

        try {
            $this->generationState = 'generating';
            $this->generateReportContent();
            $this->generationState = 'ready';
        } catch (Throwable $exception) {
            report($exception);
            $this->generationState = 'failed';
        }
    }

    public function getTitle(): string|Htmlable
    {
        return $this->reporter?->getReportPageName()
            ?? static::$title
            ?? (string) str(class_basename(static::class))
                ->kebab()
                ->replace('-', ' ')
                ->ucwords();
    }

    /**
     * Load all report data from cache and set language properties.
     * Returns false if the reporter is missing.
     */
    protected function initializeReport(): bool
    {
        $this->reporter = $this->reporterKey ? Cache::get($this->reporterKey) : null;
        $this->reportData = $this->reportDataKey ? Cache::get($this->reportDataKey) ?? [] : [];
        $this->reportConfig = $this->reportConfigKey ? Cache::get($this->reportConfigKey) ?? [] : [];

        if (! $this->reporter) {
            return false;
        }

        $this->lang = $this->reportConfig['lang'] ?? App::getLocale();
        $this->dir = $this->reportConfig['dir']
            ?? (in_array($this->lang, self::RTL_LANGUAGES) ? 'rtl' : 'ltr');

        return true;
    }

    protected function generateReportContent(): void
    {
        if ($this->reporter->getShowHtml()) {
            $this->generateHtmlReport();

            return;
        }

        $this->generatePdfReport();
    }

    /**
     * Get the common data array to be passed to the Blade view.
     */
    protected function getReportViewData(?LaravelMpdf $pdfInstance = null): array
    {
        $data = [
            ...$this->reportConfig,
            'lang' => $this->lang,
            'dir' => $this->dir,
            '__reporter' => $this->reporter,
        ];

        if ($pdfInstance) {
            $data['__mpdf'] = $pdfInstance->getMpdf();
        }

        return $data;
    }

    /**
     * Generate a simple base64-encoded HTML report.
     */
    protected function generateHtmlReport(): void
    {
        $html = View::make(
            view: $this->reporter->getReportView(),
            data: $this->reportData,
            mergeData: $this->getReportViewData()
        )->render();

        $this->base64Pdf = base64_encode($html);
    }

    /**
     * Generate a base64-encoded PDF report using mPDF.
     */
    protected function generatePdfReport(): void
    {
        ini_set('pcre.backtrack_limit', 10000000);
        ini_set('memory_limit', '512M');

        $config = array_merge($this->getDefaultMpdfConfig(), $this->reportConfig);
        $pdf = new LaravelMpdf($config);

        $htmlBeforBodyOpen = View::make(
            view: $this->reporter->getReportView(),
            data: $this->reportData,
            mergeData: $this->getReportViewData($pdf),
        )->render();

        $pos = stripos($htmlBeforBodyOpen, '</body>');
        if ($pos !== false) {
            $htmlWithoutBodyClose = substr($htmlBeforBodyOpen, 0, $pos);
        } else {
            $htmlWithoutBodyClose = $htmlBeforBodyOpen;
        }

        $mpdf = $pdf->getEngine();

        $mpdf->WriteHTML($htmlWithoutBodyClose);
        $mpdf->WriteHTML('<div class="container">');

        /** this will create pages and write it directly to mpdf by WriteHtml */
        $reportData = array_merge(
            $this->reportData,
            $this->getReportViewData($pdf)
        );

        $this->reporter->makeContent($mpdf, $reportData);

        if ($this->reporter) {
            $this->reporter->reportAfter($mpdf, $reportData);
        }

        $mpdf->WriteHTML('</div></body></html>');

        if (! App::environment('testing')) {
            $password = config('fb-report.pdf_password');
            $mpdf->SetProtection(['copy', 'print'], '', $password);
        }

        $this->base64Pdf = base64_encode($pdf->output());
    }

    /**
     * Returns the default configuration for mPDF.
     */
    protected function getDefaultMpdfConfig(): array
    {
        return [
            'mode' => 'utf-8',
            'format' => config('fb-report.pdf_format', 'A4'),
            'orientation' => config('fb-report.pdf_orientation', 'P'),
            'direction' => $this->dir,
            'margin_header' => 5,
            'margin_footer' => 5,
            'margin_top' => 5,
            'useSubstitutions' => true,
            'custom_font_dir' => config('fb-report.font_dir') ?: __DIR__.'/../../resources/fonts/',
            'custom_font_data' => config('fb-report.fonts'),
        ];
    }
}
