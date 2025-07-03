<?php

namespace Tests\Feature;

use App\Models\Good;
use App\Helpers\CurrencyHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricesApiTest extends TestCase
{
    use RefreshDatabase;

    const BREAD_RUB = 150;
    const MILK_RUB = 9000;
    public $usd, $eur;

    public function setUp(): void
    {
        parent::setUp();

        $rates = CurrencyHelper::exchangeRates();
        $this->usd = $rates['USD'];
        $this->eur = $rates['EUR'];

        // Заполняем тестовые данные
        Good::factory()->create([
            'id' => 1,
            'title' => 'Bread',
            'rub' => self::BREAD_RUB, // 150 RUB
        ]);

        Good::factory()->create([
            'id' => 2,
            'title' => 'Milk',
            'rub' => self::MILK_RUB, // 9000 RUB
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
                    ['id' => 1, 'title' => 'Bread', 'price' => number_format(self::BREAD_RUB, 0, '', ' ') . ' ₽'],
                    ['id' => 2, 'title' => 'Milk',  'price' => number_format(self::MILK_RUB , 0, '', ' ') . ' ₽'],
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
                    ['id' => 1, 'title' => 'Bread', 'price' => '$' . number_format(self::BREAD_RUB / $this->usd, 2, '.', '')],
                    ['id' => 2, 'title' => 'Milk',  'price' => '$' . number_format(self::MILK_RUB /  $this->usd, 2, '.', '')],
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
                    ['id' => 1, 'title' => 'Bread', 'price' => '€' . number_format(self::BREAD_RUB / $this->eur, 2, '.', '')],
                    ['id' => 2, 'title' => 'Milk',  'price' => '€' . number_format(self::MILK_RUB /  $this->eur, 2, '.', '')],
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
