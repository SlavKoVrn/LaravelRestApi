<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_user_can_access_profile()
    {
        // Создаем пользователя
        $user = User::factory()->create();

        // Генерируем Sanctum токен
        $token = $user->createToken('auth_token')->plainTextToken;

        // Делаем GET-запрос с токеном
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profile');

        // Проверяем ответ
        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_profile()
    {
        $response = $this->getJson('/api/profile');

        // Ожидаем ошибку 401 - Unauthorized
        $response->assertStatus(401);
    }
}
