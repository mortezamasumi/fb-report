<?php

use Illuminate\Testing\TestResponse;
use Mortezamasumi\FbReport\Tests\TestCase;
use Symfony\Component\DomCrawler\Crawler;

uses(TestCase::class)->in(__DIR__);

function getDecodedIframeContent(TestResponse $response): string
{
    $crawler = new Crawler($response->getContent());
    $iframeNode = $crawler->filter('iframe');

    // Get the current test case to run assertions
    $test = test();

    $test->assertCount(1, $iframeNode, 'Expected to find one iframe on the page.');

    $src = $iframeNode->attr('src');
    $test->assertNotNull($src, 'Iframe src attribute should not be null.');

    $parts = explode(',', $src, 2);
    $test->assertCount(2, $parts, 'The src attribute format is incorrect (expected data:...,base64).');

    $decodedContent = base64_decode($parts[1]);
    $test->assertNotFalse($decodedContent, 'Failed to base64_decode the iframe content.');

    $parser = new Smalot\PdfParser\Parser();

    $pdfPath = storage_path('app/tmp_' . uniqid() . '.pdf');
    file_put_contents($pdfPath, $decodedContent);

    $pdf = $parser->parseFile($pdfPath);

    $text = $pdf->getText();

    unlink($pdfPath);

    return $text;
}
