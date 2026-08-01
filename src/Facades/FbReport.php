<?php

namespace Mortezamasumi\FbReport\Facades;

use Closure;
use Illuminate\Support\Facades\Facade;
use Mortezamasumi\FbReport\Reports\Reporter;

/**
 * @method static void generateReport(?Reporter $reporter = null, array|Closure $reportData = [], array $reportConfig = [])
 *
 * @see \Mortezamasumi\FbReport\FbReport
 */
class FbReport extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Mortezamasumi\FbReport\FbReport::class;
    }
}
