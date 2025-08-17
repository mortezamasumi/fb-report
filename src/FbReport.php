<?php

namespace Mortezamasumi\FbReport;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Mortezamasumi\FbReport\Reports\Reporter;
use Mortezamasumi\FbReport\Reports\ReportPage;

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

        $validity = now()->addSeconds(60);

        $reporterKey = Str::random(64);
        Cache::put($reporterKey, $reporter, $validity);

        $reportDataKey = Str::random(64);
        Cache::put($reportDataKey, $reportData, $validity);

        $reportConfigKey = Str::random(64);
        Cache::put($reportConfigKey, $reportConfig, $validity);

        redirect(URL::signedRoute(ReportPage::getRouteName(), [
            'returnUrl' => $reporter->getReturnUrl(),
            'reporter' => $reporterKey,
            'reportData' => $reportDataKey,
            'reportConfig' => $reportConfigKey,
        ]));
    }
}
