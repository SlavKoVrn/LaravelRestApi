<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class NewsFactory extends Factory
{
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
            'content' => $this->faker->paragraph(3), // Random content with 3 paragraphs
            'active' => $this->faker->boolean(80), // 80% chance of being active (1)
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
