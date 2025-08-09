<?php

namespace Mortezamasumi\FbReport;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Arr;
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

        redirect(URL::signedRoute(ReportPage::getRouteName(), [
            'returnUrl' => $reporter->getReturnUrl(),
        ]))
            ->with('reporter', $reporter)
            ->with('reportData', $reportData)
            ->with('reportConfig', $reportConfig);
    }
}
