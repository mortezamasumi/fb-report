<?php

use Filament\Actions\Testing\TestAction;
use Livewire\Features\SupportTesting\Testable;
use Mortezamasumi\FbReport\Reports\ReportPage as PackageReportPage;
use Mortezamasumi\FbReport\Tests\Services\Category;
use Mortezamasumi\FbReport\Tests\Services\FailingPostReporter;
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

it('uses the loading screen by default and renders the loading shell first', function () {
    /** @var Pest $this */
    $this
        ->actingAs(User::factory()->create())
        ->livewire(ListPosts::class)
        ->callAction('loading-report')
        ->assertRedirect()
        ->tap(function ($response) {
            parse_str((string) parse_url($response->effects['redirect'], PHP_URL_QUERY), $query);

            expect($query['showLoadingScreen'] ?? null)->toBe('1');

            $this
                ->get($response->effects['redirect'])
                ->assertSuccessful()
                ->assertSee('wire:init="generateReport"', false)
                ->assertSee(__('fb-report::fb-report.preparing'))
                ->assertDontSee('data:application/pdf');
        });
});

it('uses the loading screen by default for bulk actions', function () {
    $posts = Post::all()->take(2);

    /** @var Pest $this */
    $this
        ->actingAs(User::factory()->create())
        ->livewire(ListPosts::class)
        ->selectTableRecords($posts->modelKeys())
        ->assertActionVisible(TestAction::make('loading-bulk-report')->table()->bulk())
        ->callAction(TestAction::make('loading-bulk-report')->table()->bulk())
        ->assertRedirect()
        ->tap(function ($response) {
            parse_str((string) parse_url($response->effects['redirect'], PHP_URL_QUERY), $query);

            expect($query['showLoadingScreen'] ?? null)->toBe('1');
        });
});

it('allows actions to opt out of the loading screen', function () {
    /** @var Pest $this */
    $this
        ->actingAs(User::factory()->create())
        ->livewire(ListPosts::class)
        ->callAction('synchronous-report')
        ->assertRedirect()
        ->tap(function ($response) {
            parse_str((string) parse_url($response->effects['redirect'], PHP_URL_QUERY), $query);

            expect($query['showLoadingScreen'] ?? null)->toBe('0');

            $this
                ->get($response->effects['redirect'])
                ->assertSuccessful()
                ->assertSee('data:application/pdf', false)
                ->assertDontSee('wire:init="generateReport"', false);
        });
});

it('generates default-loading reports in a follow-up Livewire request', function () {
    /** @var Pest $this */
    $response = $this
        ->actingAs(User::factory()->create())
        ->livewire(ListPosts::class)
        ->callAction('loading-report')
        ->assertRedirect();

    parse_str((string) parse_url($response->effects['redirect'], PHP_URL_QUERY), $query);

    $component = Testable::create(PackageReportPage::class, [], $query);

    $component
        ->assertSet('generationState', 'pending')
        ->call('generateReport')
        ->assertSet('generationState', 'ready');

    expect($component->get('base64Pdf'))->not->toBeEmpty();
});

it('shows a retry state after generation fails and completes on retry', function () {
    FailingPostReporter::$failNextGeneration = true;

    /** @var Pest $this */
    $response = $this
        ->actingAs(User::factory()->create())
        ->livewire(ListPosts::class)
        ->callAction('failing-loading-report')
        ->assertRedirect();

    parse_str((string) parse_url($response->effects['redirect'], PHP_URL_QUERY), $query);

    $component = Testable::create(PackageReportPage::class, [], $query);

    $component
        ->call('generateReport')
        ->assertSet('generationState', 'failed')
        ->assertSee(__('fb-report::fb-report.retry'));

    $component
        ->call('generateReport')
        ->assertSet('generationState', 'ready');

    expect($component->get('base64Pdf'))->not->toBeEmpty();
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
