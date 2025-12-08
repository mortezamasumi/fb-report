<?php

use Amp\Parser\Parser;
use Illuminate\Support\Facades\App;
use Mortezamasumi\FbReport\Tests\Services\Category;
use Mortezamasumi\FbReport\Tests\Services\Group;
use Mortezamasumi\FbReport\Tests\Services\Post;
use Mortezamasumi\FbReport\Tests\Services\ReportPage;
use Mortezamasumi\FbReport\Tests\Services\User;

it('can render report page', function () {
    $this
        ->livewire(ReportPage::class)
        ->assertSuccessful();
});

it('can see report action', function () {
    $this
        ->livewire(ReportPage::class)
        ->assertActionExists('page-all-report')
        ->assertActionExists('page-single-report');
});

it('can call report action', function () {
    $this
        ->actingAs(User::factory()->create())
        ->livewire(ReportPage::class)
        ->callAction('page-all-report')
        ->assertHasNoActionErrors();
});

it('can make posts report and verify content using macros', function () {
    App::setLocale('fa');

    Group::factory(1)
        ->has(Category::factory(1)
            ->has(Post::factory(1)))
        ->create();

    $this
        ->actingAs(User::factory()->create())
        ->livewire(ReportPage::class)
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
                        // ->not
                        // ->toContain($post->title)
                        // ->toContain(__jdatetime(__f_date(), $post->created_at))
                        // ->not
                        // ->toContain($post->created_at->format(__f_date()))
                        // ->toContain(__jdatetime(__f_datetime(), $post->created_at))
                        // ->not
                        // ->toContain($post->created_at->format(__f_datetime()));
                    }
                });
        });
});
