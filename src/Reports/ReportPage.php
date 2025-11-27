<?php

namespace Mortezamasumi\FbReport\Reports;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;

class ReportPage extends Page
{
    protected string $view = 'fb-report::filament.pages.report';
    protected static string|array $routeMiddleware = 'signed';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $slug = 'report';

    protected const RTL_LANGUAGES = ['fa', 'ar', 'ur', 'he'];

    public $base64Pdf;
    protected ?Reporter $reporter = null;
    protected ?array $reportData;
    protected ?array $reportConfig;
    protected ?string $returnUrl = null;
    protected string $lang;
    protected string $dir;

    public function mount(): void
    {
        if (!$this->initializeReport()) {
            redirect($this->returnUrl);

            return;
        }

        if ($this->reporter->getShowHtml()) {
            $this->generateHtmlReport();
        } else {
            $this->generatePdfReport();
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
        $this->returnUrl = request()->get('returnUrl');
        $this->reporter = Cache::get(request()->get('reporter'));
        $this->reportData = Cache::get(request()->get('reportData'));
        $this->reportConfig = Cache::get(request()->get('reportConfig'));

        if (!$this->reporter) {
            return false;
        }

        $this->lang = $this->reportConfig['lang'] ?? App::getLocale();
        $this->dir = $this->reportConfig['dir']
            ?? (in_array($this->lang, self::RTL_LANGUAGES) ? 'rtl' : 'ltr');

        return true;
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
        // ini_set('pcre.backtrack_limit', 10000000);
        // ini_set('memory_limit', '512M');

        $config = array_merge($this->getDefaultMpdfConfig(), $this->reportConfig);
        $pdf = new LaravelMpdf($config);

        // $this->reporter->mpdfBeforHtml($pdf);

        // $htmlContent = View::make(
        //     view: $this->reporter->getReportView(),
        //     data: $this->reportData,
        //     mergeData: $this->getReportViewData($pdf),
        // )->render();

        // dd($htmlContent);

        // $chunks = $this->splitHTMLIntoChunks($htmlContent, 50000);  // 50KB chunks

        // foreach ($chunks as $chunk) {
        //     $pdf->getMpdf()->WriteHTML($chunk);
        // }

        // $this->reporter->mpdfAfterHtml($pdf);

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

        $mpdf = $pdf->getMpdf();

        $mpdf->WriteHTML($htmlWithoutBodyClose);
        $mpdf->WriteHTML('<div class="container">');

        /** this will create pages and write it directly to mpdf by WriteHtml */
        $this->reporter->makeContent(
            $mpdf,
            array_merge(
                $this->reportData,
                $this->getReportViewData($pdf)
            )
        );

        $mpdf->WriteHTML('</div></body></html>');

        // SUGGESTION: Move this password to .env and config
        $password = config('fb-report.pdf_password', 'SG@%$ashgf236dShsd&*7253');
        $mpdf->SetProtection(['copy', 'print'], '', $password);

        $this->base64Pdf = base64_encode($pdf->output());
    }

    private function splitHTMLIntoChunks($html, $chunkSize = 50000)
    {
        $chunks = [];
        $currentChunk = '';

        // Split by HTML tags to maintain structure
        $parts = preg_split('/(<table\b[^>]*>.*?<\/table>|<div\b[^>]*>.*?<\/div>|<p\b[^>]*>.*?<\/p>)/si', $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        foreach ($parts as $part) {
            if (strlen($currentChunk . $part) > $chunkSize && !empty($currentChunk)) {
                $chunks[] = $currentChunk;
                $currentChunk = $part;
            } else {
                $currentChunk .= $part;
            }
        }

        if (!empty($currentChunk)) {
            $chunks[] = $currentChunk;
        }

        return $chunks;
    }

    /**
     * Returns the default configuration for mPDF.
     * SUGGESTION: Move this to a dedicated config/fb-report.php file.
     */
    protected function getDefaultMpdfConfig(): array
    {
        return [
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'direction' => $this->dir,
            'margin_header' => 5,
            'margin_footer' => 5,
            'margin_top' => 5,
            'useSubstitutions' => true,
            // SUGGESTION: Make this path configurable
            'custom_font_dir' => __DIR__ . '/../../resources/fonts/',
            'custom_font_data' => [
                'gandom' => [
                    'R' => 'Gandom.ttf',
                    'useKashida' => 75,
                    'useOTL' => 0xFF,
                ],
                'homa' => [
                    'R' => 'Homa.ttf',
                    'useKashida' => 75,
                    'useOTL' => 0xFF,
                ],
                'iran' => [
                    'R' => 'Iran.ttf',
                    'B' => 'Iran-Bold.ttf',
                    'useKashida' => 75,
                    'useOTL' => 0xFF,
                ],
                'keyhan' => [
                    'R' => 'Keyhan.ttf',
                    'B' => 'Keyhan-Bold.ttf',
                    'useKashida' => 75,
                    'useOTL' => 0xFF,
                ],
                'keyhannavaar' => [
                    'R' => 'Keyhan-Navaar.ttf',
                    'useKashida' => 75,
                    'useOTL' => 0xFF,
                ],
                'keyhanpook' => [
                    'R' => 'Keyhan-Pook.ttf',
                    'useKashida' => 75,
                    'useOTL' => 0xFF,
                ],
                'keyhansayeh' => [
                    'R' => 'Keyhan-Sayeh.ttf',
                    'useKashida' => 75,
                    'useOTL' => 0xFF,
                ],
                'koodak' => [
                    'R' => 'Koodak.ttf',
                    'useKashida' => 75,
                    'useOTL' => 0xFF,
                ],
                'lalezar' => [
                    'R' => 'Lalezar.ttf',
                    'useKashida' => 75,
                    'useOTL' => 0xFF,
                ],
                'nastaliq' => [
                    'R' => 'Nastaliq.ttf',
                    'useKashida' => 75,
                    'useOTL' => 0xFF,
                ],
                'nazli' => [
                    'R' => 'Nazli.ttf',
                    'B' => 'Nazli-Bold.ttf',
                    'useKashida' => 75,
                    'useOTL' => 0xFF,
                ],
                'parastoo' => [
                    'R' => 'Parastoo.ttf',
                    'B' => 'Parastoo-Bold.ttf',
                    'useKashida' => 75,
                    'useOTL' => 0xFF,
                ],
                'sahel' => [
                    'R' => 'Sahel.ttf',
                    'B' => 'Sahel-Bold.ttf',
                    'useKashida' => 75,
                    'useOTL' => 0xFF,
                ],
                'shabnam' => [
                    'R' => 'Shabnam.ttf',
                    'B' => 'Shabnam-Bold.ttf',
                    'useKashida' => 75,
                    'useOTL' => 0xFF,
                ],
                'shafigh' => [
                    'R' => 'Shafigh.ttf',
                    'B' => 'Shafigh-Bold.ttf',
                    'useKashida' => 75,
                    'useOTL' => 0xFF,
                ],
                'vahid' => [
                    'R' => 'Vahid.ttf',
                    'B' => 'Vahid-Bold.ttf',
                    'useKashida' => 75,
                    'useOTL' => 0xFF,
                ],
                'vazir' => [
                    'R' => 'Vazirmatn.ttf',
                    'B' => 'Vazirmatn-Bold.ttf',
                    'useKashida' => 75,
                    'useOTL' => 0xFF,
                ],
                'yaghut' => [
                    'R' => 'Yaghut.ttf',
                    'B' => 'Yaghut-Bold.ttf',
                    'useKashida' => 75,
                    'useOTL' => 0xFF,
                ],
                'yas' => [
                    'R' => 'Yas.ttf',
                    'B' => 'Yas-Bold.ttf',
                    'useKashida' => 75,
                    'useOTL' => 0xFF,
                ],
                'yermook' => [
                    'R' => 'Yermook.ttf',
                    'B' => 'Yermook-Bold.ttf',
                    'useKashida' => 75,
                    'useOTL' => 0xFF,
                ],
                'zar' => [
                    'R' => 'Zar.ttf',
                    'useKashida' => 75,
                    'useOTL' => 0xFF,
                ],
                'ziba' => [
                    'R' => 'Ziba.ttf',
                    'B' => 'Ziba-Bold.ttf',
                    'useKashida' => 75,
                    'useOTL' => 0xFF,
                ],
                'titr' => [
                    'R' => 'Titr.ttf',
                    'B' => 'Titr-Bold.ttf',
                    'useKashida' => 75,
                    'useOTL' => 0xFF,
                ],
            ],
        ];
    }
}
