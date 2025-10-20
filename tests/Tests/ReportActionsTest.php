<?php

use Filament\Actions\Testing\TestAction;
use Mortezamasumi\FbEssentials\Facades\FbPersian;
use Mortezamasumi\FbReport\Tests\Services\ListPosts;
use Mortezamasumi\FbReport\Tests\Services\Post;
use Mortezamasumi\FbReport\Tests\Services\PostReport;
use Mortezamasumi\FbReport\Tests\Services\PostResource;
use Mortezamasumi\FbReport\Tests\Services\PostsReport;
use Mortezamasumi\FbReport\Tests\Services\User;
use Symfony\Component\DomCrawler\Crawler;

it('can render list page', function () {
    $this
        ->actingAs(User::factory()->create())
        ->get(PostResource::getUrl('index'))
        ->assertSuccessful();
});

it('can report using action in list page', function () {
    Post::factory(5)->create();

    $this
        ->actingAs(User::factory()->create())
        ->livewire(ListPosts::class)
        ->assertActionExists('report')
        ->callAction('report')
        ->assertRedirect()
        ->tap(function ($response) {
            $this
                ->get($response->effects['redirect'])
                ->assertSuccessful()
                ->tap(function ($response) {
                    $posts = Post::all();

                    $crawler = new Crawler($response->getContent());

                    $iframeNode = $crawler->filter('iframe');
                    $this->assertCount(1, $iframeNode, 'Expected to find one iframe on the page.');

                    $src = $iframeNode->attr('src');
                    $this->assertNotNull($src, 'Iframe src attribute should not be null.');

                    $parts = explode(',', $src, 2);
                    $this->assertCount(2, $parts, 'The src attribute format is correct.');

                    $decodedContent = base64_decode($parts[1]);

                    foreach ($posts as $post) {
                        expect($decodedContent)
                            ->toContain('Title1')
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
                    }
                });
        });
});

it('can report using action in record actions', function () {
    $posts = Post::factory(5)->create();

    $post = $posts->skip(2)->first();

    $this
        ->actingAs(User::factory()->create())
        ->livewire(ListPosts::class)
        ->assertTableActionExists('record-report')
        ->callAction(TestAction::make('record-report')->table($post))
        ->assertRedirect()
        ->tap(function ($response) use ($post) {
            $this
                ->get($response->effects['redirect'])
                ->assertSuccessful()
                ->tap(function ($response) use ($post) {
                    $crawler = new Crawler($response->getContent());

                    $iframeNode = $crawler->filter('iframe');
                    $this->assertCount(1, $iframeNode, 'Expected to find one iframe on the page.');

                    $src = $iframeNode->attr('src');
                    $this->assertNotNull($src, 'Iframe src attribute should not be null.');

                    $parts = explode(',', $src, 2);
                    $this->assertCount(2, $parts, 'The src attribute format is correct.');

                    $decodedContent = base64_decode($parts[1]);

                    expect($decodedContent)
                        ->toContain('Title1')
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

it('can report using toolbar action', function () {
    Post::factory(5)->create();

    $this
        ->actingAs(User::factory()->create())
        ->livewire(ListPosts::class)
        ->selectTableRecords(Post::pluck('id')->toArray())
        ->assertActionVisible(TestAction::make('bulk-report')->table()->bulk())
        ->callAction(TestAction::make('bulk-report')->table()->bulk())
        ->assertRedirect()
        ->tap(function ($response) {
            $this
                ->get($response->effects['redirect'])
                ->assertSuccessful()
                ->tap(function ($response) {
                    $posts = Post::all();

                    $crawler = new Crawler($response->getContent());

                    $iframeNode = $crawler->filter('iframe');
                    $this->assertCount(1, $iframeNode, 'Expected to find one iframe on the page.');

                    $src = $iframeNode->attr('src');
                    $this->assertNotNull($src, 'Iframe src attribute should not be null.');

                    $parts = explode(',', $src, 2);
                    $this->assertCount(2, $parts, 'The src attribute format is correct.');

                    $decodedContent = base64_decode($parts[1]);

                    foreach ($posts as $post) {
                        expect($decodedContent)
                            ->toContain('Title1')
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
                    }
                });
        });
});

it('can report using header action', function () {
    Post::factory(5)->create();

    $this
        ->actingAs(User::factory()->create())
        ->livewire(ListPosts::class)
        ->assertActionVisible(TestAction::make('header-report')->table())
        ->callAction(TestAction::make('header-report')->table())
        ->assertRedirect()
        ->tap(function ($response) {
            $this
                ->get($response->effects['redirect'])
                ->assertSuccessful()
                ->tap(function ($response) {
                    $posts = Post::all();

                    $crawler = new Crawler($response->getContent());

                    $iframeNode = $crawler->filter('iframe');
                    $this->assertCount(1, $iframeNode, 'Expected to find one iframe on the page.');

                    $src = $iframeNode->attr('src');
                    $this->assertNotNull($src, 'Iframe src attribute should not be null.');

                    $parts = explode(',', $src, 2);
                    $this->assertCount(2, $parts, 'The src attribute format is correct.');

                    $decodedContent = base64_decode($parts[1]);

                    foreach ($posts as $post) {
                        expect($decodedContent)
                            ->toContain('Title1')
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
                    }
                });
        });
});

it('can report using page action using useModel', function () {
    Post::factory(5)->create();

    $this
        ->actingAs(User::factory()->create())
        ->livewire(PostsReport::class)
        ->callAction('report')
        ->assertRedirect()
        ->tap(function ($response) {
            $this
                ->get($response->effects['redirect'])
                ->assertSuccessful()
                ->tap(function ($response) {
                    $posts = Post::all();

                    $crawler = new Crawler($response->getContent());

                    $iframeNode = $crawler->filter('iframe');
                    $this->assertCount(1, $iframeNode, 'Expected to find one iframe on the page.');

                    $src = $iframeNode->attr('src');
                    $this->assertNotNull($src, 'Iframe src attribute should not be null.');

                    $parts = explode(',', $src, 2);
                    $this->assertCount(2, $parts, 'The src attribute format is correct.');

                    $decodedContent = base64_decode($parts[1]);

                    foreach ($posts as $post) {
                        expect($decodedContent)
                            ->toContain('Title1')
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
                    }
                });
        });
});

it('can report using page action using useRecord', function () {
    $posts = Post::factory(5)->create();

    $post = $posts->skip(2)->first();

    $this
        ->actingAs(User::factory()->create())
        ->livewire(PostReport::class)
        ->mountAction('report')
        ->callMountedAction()
        ->assertRedirect()
        ->tap(function ($response) use ($post) {
            $this
                ->get($response->effects['redirect'])
                ->assertSuccessful()
                ->tap(function ($response) use ($post) {
                    $crawler = new Crawler($response->getContent());

                    $iframeNode = $crawler->filter('iframe');
                    $this->assertCount(1, $iframeNode, 'Expected to find one iframe on the page.');

                    $src = $iframeNode->attr('src');
                    $this->assertNotNull($src, 'Iframe src attribute should not be null.');

                    $parts = explode(',', $src, 2);
                    $this->assertCount(2, $parts, 'The src attribute format is correct.');

                    $decodedContent = base64_decode($parts[1]);

                    expect($decodedContent)
                        ->toContain('Title1')
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
