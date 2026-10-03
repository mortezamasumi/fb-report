<?php

namespace Mortezamasumi\FbReport\Tests\Services;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

class FailingPostReporter extends PostReporter
{
    public static bool $failNextGeneration = false;

    public function getMainHtml(array $data, Collection $titles, Collection $rows): string|Htmlable
    {
        if (static::$failNextGeneration) {
            static::$failNextGeneration = false;

            throw new \RuntimeException('Simulated report rendering failure.');
        }

        return parent::getMainHtml($data, $titles, $rows);
    }
}
