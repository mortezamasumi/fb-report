<?php

use Mortezamasumi\FbReport\Reports\Reporter;
use Mortezamasumi\FbReport\Tests\Services\FinalHookReporter;

it('provides a final report hook on every reporter', function () {
    expect(method_exists(Reporter::class, 'getReportAfterHtml'))->toBeTrue();
});

it('allows reporters to customize final report html', function () {
    $reporter = (new ReflectionClass(FinalHookReporter::class))->newInstanceWithoutConstructor();

    expect($reporter->getReportAfterHtml([]))
        ->toBe('<div id="final-report-hook">final report hook</div>');
});
