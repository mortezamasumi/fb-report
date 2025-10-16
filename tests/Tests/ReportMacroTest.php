<?php

use Illuminate\Support\Facades\App;
use Mortezamasumi\FbEssentials\Facades\FbPersian;
use Mortezamasumi\FbReport\Tests\Services\Post;
use Mortezamasumi\FbReport\Tests\Services\PostsReport;
use Mortezamasumi\FbReport\Tests\Services\User;
use Symfony\Component\DomCrawler\Crawler;

it('can render report page', function () {
    $this
        ->livewire(PostsReport::class)
        ->assertSuccessful();
});

it('can see report action', function () {
    $this
        ->livewire(PostsReport::class)
        ->assertActionExists('report');
});

it('can call report action', function () {
    $this
        ->actingAs(User::factory()->create())
        ->livewire(PostsReport::class)
        ->mountAction('report')
        ->callMountedAction()
        ->assertHasNoActionErrors();
});

it('can make posts report and verify content using macros', function () {
    App::setLocale('fa');

    Post::factory()->create();

    $this
        ->actingAs(User::factory()->create())
        ->livewire(PostsReport::class)
        ->mountAction('report')
        ->callMountedAction()
        ->assertRedirect()
        ->tap(function ($response) {
            $this
                ->get($response->effects['redirect'])
                ->assertSuccessful()
                ->tap(function ($response) {
                    $post = Post::first();

                    $crawler = new Crawler($response->getContent());

                    $iframeNode = $crawler->filter('iframe');
                    $this->assertCount(1, $iframeNode, 'Expected to find one iframe on the page.');

                    $src = $iframeNode->attr('src');
                    $this->assertNotNull($src, 'Iframe src attribute should not be null.');

                    $parts = explode(',', $src, 2);
                    $this->assertCount(2, $parts, 'The src attribute format is correct.');

                    $base64Part = $parts[1];
                    $decodedContent = base64_decode($base64Part);

                    expect($decodedContent)
                        ->toContain('Title1')
                        ->toContain(FbPersian::digit($post->title1))
                        ->not
                        ->toContain($post->title1)
                        ->toContain($post->title2)
                        ->toContain(FbPersian::jDateTime(__('fb-essentials::fb-essentials.date_format.simple'), $post->date1))
                        ->not
                        ->toContain(FbPersian::jDateTime(__('fb-essentials::fb-essentials.date_format.time_simple'), $post->date1))
                        ->not
                        ->toContain($post->date1)
                        ->toContain(FbPersian::jDateTime(__('fb-essentials::fb-essentials.date_format.time_simple'), $post->date2))
                        ->not
                        ->toContain($post->date2);
                });
        });
});
