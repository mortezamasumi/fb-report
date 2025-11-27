<?php

use Mortezamasumi\FbReport\Tests\Services\Category;
use Mortezamasumi\FbReport\Tests\Services\Group;
use Mortezamasumi\FbReport\Tests\Services\Post;
use Mortezamasumi\FbReport\Tests\Services\ReportPage;
use Mortezamasumi\FbReport\Tests\Services\User;

return;
beforeEach(function () {
    Group::factory(3)
        ->has(Category::factory(3)
            ->has(Post::factory(5)))
        ->create();

    $this->actingAs(User::factory()->create());
});

describe('Posts on Group model using GroupReporter', function () {
    it('can make report using group/sub-group by Group model', function () {
        $this
            ->livewire(ReportPage::class)
            ->assertActionExists('page-group-report')
            ->callAction('page-group-report')
            ->assertRedirect()
            ->tap(function ($response) {
                $this
                    ->get($response->effects['redirect'])
                    ->assertSuccessful()
                    ->tap(function ($response) {
                        $decodedContent = getDecodedIframeContent($response);

                        foreach (Post::all() as $post) {
                            expect($decodedContent)
                                ->toContain($post->category->group->title)
                                ->toContain($post->category->title)
                                ->toContain($post->title);
                        }
                    });
            });
    });
});

describe('Posts on Categories model using CategoryReporter both states', function () {
    it('can make report using group/sub-group by useRecord set Group instance', function () {
        $this
            ->livewire(ReportPage::class)
            ->assertActionExists('page-category-report')
            ->callAction('page-category-report')
            ->assertRedirect()
            ->tap(function ($response) {
                $this
                    ->get($response->effects['redirect'])
                    ->assertSuccessful()
                    ->tap(function ($response) {
                        $decodedContent = getDecodedIframeContent($response);

                        $posts = Post::whereHas('category.group', function ($query) {
                            $query->where('id', Group::first()->id);
                        })->get();

                        foreach ($posts as $post) {
                            expect($decodedContent)
                                ->toContain($post->category->group->title)
                                ->toContain($post->category->title)
                                ->toContain($post->title);
                        }
                    });
            });
    });

    it('can make report using group/sub-group by Category model', function () {
        $this
            ->livewire(ReportPage::class)
            ->assertActionExists('page-categories-report')
            ->callAction('page-categories-report')
            ->assertRedirect()
            ->tap(function ($response) {
                $this
                    ->get($response->effects['redirect'])
                    ->assertSuccessful()
                    ->tap(function ($response) {
                        $decodedContent = getDecodedIframeContent($response);

                        foreach (Post::all() as $post) {
                            expect($decodedContent)
                                ->toContain($post->category->group->title)
                                ->toContain($post->category->title)
                                ->toContain($post->title);
                        }
                    });
            });
    });
});
