<?php

namespace Mortezamasumi\FbReport;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Mortezamasumi\FbReport\Reports\Reporter;
use Mortezamasumi\FbReport\Reports\ReportPage;
use Closure;

class FbReport
{
    public function generateReport(
        ?Reporter $reporter = null,
        array|Closure $reportData = [],
        array $reportConfig = [],
    ): void {
        if ($reportData instanceof Closure || is_callable($reportData)) {
            $reportData = Arr::wrap($reportData());
        }

        $timeTokeep = App::isProduction() ? 60 : 600;

        Cache::put($reporterKey = Str::random(64), $reporter, now()->addSeconds($timeTokeep));
        Cache::put($reportDataKey = Str::random(64), $reportData, now()->addSeconds($timeTokeep));
        Cache::put($reportConfigKey = Str::random(64), $reportConfig, now()->addSeconds($timeTokeep));

        redirect(URL::signedRoute(ReportPage::getRouteName(), [
            'returnUrl' => $reporter->getReturnUrl(),
            'reporter' => $reporterKey,
            'reportData' => $reportDataKey,
            'reportConfig' => $reportConfigKey,
        ]));
    }
}
