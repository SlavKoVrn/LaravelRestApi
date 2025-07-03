<?php

namespace Tests\Feature;

use App\Models\Good;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PricesApiTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // Заполняем тестовые данные
        Good::factory()->create([
            'id' => 1,
            'title' => 'Bread',
            'rub' => 150, // 150 RUB
        ]);

        Good::factory()->create([
            'id' => 2,
            'title' => 'Milk',
            'rub' => 9000, // 9000 RUB
        ]);
    }

    /** @test */
    public function it_returns_goods_in_rub_format_by_default()
    {
        $response = $this->getJson('/api/prices');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [[
                    'id', 'title', 'price'
                ]]            ])
            ->assertJson([
                'data' => [
                    ['id' => 1, 'title' => 'Bread', 'price' => '150 ₽'],
                    ['id' => 2, 'title' => 'Milk', 'price' => '9 000 ₽'],
                ]
            ]);
    }

    /** @test */
    public function it_returns_goods_in_usd_format_when_currency_is_usd()
    {
        $response = $this->getJson('/api/prices?currency=USD');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['id' => 1, 'title' => 'Bread', 'price' => '$1.67'],
                    ['id' => 2, 'title' => 'Milk', 'price' => '$100.00'],
                ]
            ]);
    }

    /** @test */
    public function it_returns_goods_in_eur_format_when_currency_is_eur()
    {
        $response = $this->getJson('/api/prices?currency=EUR');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['id' => 1, 'title' => 'Bread', 'price' => '€1.50'],
                    ['id' => 2, 'title' => 'Milk', 'price' => '€90.00'],
                ]
            ]);
    }

    /** @test */
    public function it_handles_unknown_currency_gracefully()
    {
        $response = $this->getJson('/api/prices?currency=JPY');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['id' => 1, 'title' => 'Bread', 'price' => '150 ₽'],
                ]
            ]);
    }

}
