<?php

namespace Database\Factories;

use App\Models\Good;
use Illuminate\Database\Eloquent\Factories\Factory;

class GoodFactory extends Factory
{
    protected $model = Good::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $this->faker = \Faker\Factory::create('ru_RU');

        return [
            'title' => $this->faker->sentence(5), // Random title with 5 words
            'rub' => random_int(10, 1000),
        ];
    }
}
