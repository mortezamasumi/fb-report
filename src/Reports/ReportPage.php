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
    public $base64Pdf;
    protected ?Reporter $reporter = null;
    protected ?array $reportData;
    protected ?array $reportConfig;
    protected ?string $returnUrl = null;

    public function mount(): void
    {
        $this->returnUrl = request()->get('returnUrl');
        $this->reporter = Cache::get(request()->get('reporter'));
        $this->reportData = Cache::get(request()->get('reportData'));
        $this->reportConfig = Cache::get(request()->get('reportConfig'));

        if (! $this->reporter) {
            redirect($this->returnUrl);

            return;
        }

        $lang = $this->reportConfig['lang'] ?? App::getLocale();
        $dir = $this->reportConfig['dir'] ?? in_array($lang, ['fa', 'ar', 'ur', 'he']) ? 'rtl' : 'ltr';

        if ($this->reporter->getShowHtml()) {
            $this->base64Pdf = base64_encode(
                View::make(
                    view: $this->reporter->getReportView(),
                    data: $this->reportData,
                    mergeData: [
                        ...$this->reportConfig,
                        'lang' => $lang,
                        'dir' => $dir,
                        '__reporter' => $this->reporter
                    ],
                )->render()
            );
        } else {
            $defaultConfig = [
                'mode' => 'utf-8',
                'format' => 'A4',
                'orientation' => 'P',
                'direction' => $dir,
                'margin_header' => 5,
                'margin_footer' => 5,
                'margin_top' => 5,
                'useSubstitutions' => true,
                'custom_font_dir' => __DIR__.'/../../resources/fonts/',  // don't forget the trailing slash!
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
                ],
            ];

            $pdf = new LaravelMpdf(array_merge($defaultConfig, $this->reportConfig));

            $this->reporter->mpdfBeforHtml($pdf);

            $pdf->getMpdf()->WriteHTML(View::make(
                view: $this->reporter->getReportView(),
                data: $this->reportData,
                mergeData: [
                    ...$this->reportConfig,
                    'lang' => $lang,
                    'dir' => $dir,
                    '__reporter' => $this->reporter,
                    '__mpdf' => $pdf->getMpdf(),
                ],
            )->render());

            $this->reporter->mpdfAfterHtml($pdf);

            $pdf->getMpdf()->SetProtection(array('copy', 'print'), '', 'SG@%$ashgf236dShsd&*7253');

            $this->base64Pdf = base64_encode($pdf->output());
        }
    }

    public function getTitle(): string|Htmlable
    {
        return $this->reporter?->getReportPageName() ?? static::$title ?? (string) str(class_basename(static::class))
            ->kebab()
            ->replace('-', ' ')
            ->ucwords();;
    }
}
