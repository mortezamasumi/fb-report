<?php

namespace Mortezamasumi\FbReport\Tests\Services;

class FinalHookReporter extends PostReporter
{
    public function getReportAfterHtml($data): string
    {
        return '<div id="final-report-hook">final report hook</div>';
    }
}
