<?php

use Filament\Actions\Testing\TestAction;
use Mortezamasumi\FbReport\Tests\Services\Category;
use Mortezamasumi\FbReport\Tests\Services\Group;
use Mortezamasumi\FbReport\Tests\Services\ListPosts;
use Mortezamasumi\FbReport\Tests\Services\Post;
use Mortezamasumi\FbReport\Tests\Services\PostResource;
use Mortezamasumi\FbReport\Tests\Services\ReportPage;
use Mortezamasumi\FbReport\Tests\Services\User;

beforeEach(function () {
    Group::factory(3)
        ->has(Category::factory(3)
            ->has(Post::factory(5)))
        ->create();
});

it('can render list page', function () {
    /** @var Pest $this */
    $this
        ->actingAs(User::factory()->create())
        ->get(PostResource::getUrl('index'))
        ->assertSuccessful();
});

it('can report using action in list page', function () {
    /** @var Pest $this */
    $this
        ->actingAs(User::factory()->create())
        ->livewire(ListPosts::class)
        ->assertActionExists('list-report')
        ->callAction('list-report')
        ->assertRedirect()
        ->tap(function ($response) {
            $this
                ->get($response->effects['redirect'])
                ->assertSuccessful()
                ->tap(function ($response) {
                    $decodedContent = getDecodedIframeContent($response);

                    foreach (Post::all() as $post) {
                        expect($decodedContent)
                            ->toContain('Title')
                            ->toContain(__digit($post->title));
                    }
                });
        });
});

it('can report using action in record actions', function () {
    $post = Post::latest('title')->first();

    /** @var Pest $this */
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
                    $decodedContent = getDecodedIframeContent($response);

                    expect($decodedContent)
                        ->toContain('Title')
                        ->toContain(__digit($post->title));
                });
        });
});

it('can report using toolbar action', function () {
    $posts = Post::all()->shuffle()->take(30);

    /** @var Pest $this */
    $this
        ->actingAs(User::factory()->create())
        ->livewire(ListPosts::class)
        ->selectTableRecords($posts->pluck('id')->toArray())
        ->assertActionVisible(TestAction::make('bulk-report')->table()->bulk())
        ->callAction(TestAction::make('bulk-report')->table()->bulk())
        ->assertRedirect()
        ->tap(function ($response) use ($posts) {
            $this
                ->get($response->effects['redirect'])
                ->assertSuccessful()
                ->tap(function ($response) use ($posts) {
                    $decodedContent = getDecodedIframeContent($response);

                    foreach ($posts as $post) {
                        expect($decodedContent)
                            ->toContain('Title')
                            ->toContain(__digit($post->title));
                    }
                });
        });
});

it('can report using header action', function () {
    /** @var Pest $this */
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
                    $decodedContent = getDecodedIframeContent($response);

                    foreach (Post::all() as $post) {
                        expect($decodedContent)
                            ->toContain('Title')
                            ->toContain(__digit($post->title));
                    }
                });
        });
});

it('can report using page action using useModel', function () {
    /** @var Pest $this */
    $this
        ->actingAs(User::factory()->create())
        ->livewire(ReportPage::class)
        ->assertTableActionExists('page-all-report')
        ->callAction('page-all-report')
        ->assertRedirect()
        ->tap(function ($response) {
            $this
                ->get($response->effects['redirect'])
                ->assertSuccessful()
                ->tap(function ($response) {
                    $decodedContent = getDecodedIframeContent($response);

                    foreach (Post::all() as $post) {
                        expect($decodedContent)
                            ->toContain('Title')
                            ->toContain(__digit($post->title));
                    }
                });
        });
});

it('can report using page action using useRecord', function () {
    /** @var Pest $this */
    $this
        ->actingAs(User::factory()->create())
        ->livewire(ReportPage::class)
        ->assertTableActionExists('page-single-report')
        ->callAction('page-single-report')
        ->assertRedirect()
        ->tap(function ($response) {
            $this
                ->get($response->effects['redirect'])
                ->assertSuccessful()
                ->tap(function ($response) {
                    $decodedContent = getDecodedIframeContent($response);

                    $post = Post::latest('title')->first();

                    expect($decodedContent)
                        ->toContain('Title')
                        ->toContain(__digit($post->title));
                });
        });
});
