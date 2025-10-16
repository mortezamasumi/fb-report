<?php

namespace Mortezamasumi\FbReport\Tests\Services;

use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    public function definition()
    {
        return [
            'title1' => fake()->bothify('????????-#######'),
            'title2' => fake()->name(),
            'date1' => fake()->dateTime(),
            'date2' => fake()->dateTime(),
        ];
    }
}
