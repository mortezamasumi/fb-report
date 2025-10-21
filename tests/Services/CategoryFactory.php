<?php

namespace Mortezamasumi\FbReport\Tests\Services;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    public function definition()
    {
        return [
            'title' => fake()->bothify('category-????????-#######'),
        ];
    }
}
