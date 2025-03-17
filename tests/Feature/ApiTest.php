<?php

namespace Tests\Feature;

use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase; // Ensures the database is reset after each test

    private $faker;

    function __construct(string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->faker = \Faker\Factory::create();
    }

    /** @test */
    public function it_can_list_all_news()
    {
        News::factory()->count(3)->create();

        $response = $this->getJson('/api/news');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    /** @test */
    public function it_can_create_a_new_news()
    {
        $postData = [
            'title' => $this->faker->sentence(5),
            'content' => $this->faker->paragraph(3),
            'active' => $this->faker->boolean(80),
        ];

        $response = $this->postJson('/api/news', $postData);

        $response->assertStatus(201)
            ->assertJson($postData);

        $this->assertDatabaseHas('news', $postData);
    }

    /** @test */
    public function it_can_show_a_specific_news()
    {
        $news = News::factory()->create();

        $response = $this->getJson("/api/news/{$news->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $news->id,
                'title' => $news->title,
            ]);
    }

    /** @test */
    public function it_can_update_a_news()
    {
        $news = News::factory()->create();

        $updatedData = [
            'title' => $this->faker->sentence(5),
            'content' => $this->faker->paragraph(3),
            'active' => $this->faker->boolean(80),
        ];

        $response = $this->putJson("/api/news/{$news->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJson($updatedData);

        $this->assertDatabaseHas('news', $updatedData);
    }

    /** @test */
    public function it_can_delete_a_post()
    {
        $news = News::factory()->create();

        $response = $this->deleteJson("/api/news/{$news->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('news', ['id' => $news->id]);
    }

}
