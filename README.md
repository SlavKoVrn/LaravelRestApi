<h2>АО «Спутник»</h2>

<h3>Вакансия: Backend-разработчик</h3>

```php
Создайте GET-эндпоинт /api/prices, который возвращает JSON-список товаров (`id`, title, `price`).
При передаче параметра currency (`RUB`, USD, EUR`) — возвращайте цену с конвертацией (RUB = 1; USD = 90; EUR = 100) 
и форматированием (`$1.50, €2.00, `1 200 ₽`).
Используйте Laravel 8+ и Laravel Resource. Ответ — в формате JSON.
```

<h4>1. Создайте модель и миграцию</h4>

```bash
php artisan make:model Good -mf

// database/migrations/xxxx_xx_xx_create_goods_table.php

public function up()
{
    Schema::create('goods', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->unsignedInteger('rub'); // цена в рублях
    });
}
```

выполните миграцию:

```bash
php artisan migrate
```

<h4>2. заполнение тестовыми данными</h4>

создайте фабрику `database/factories/GoodFactory.php`

```bash
php artisan make:factory GoodFactory --model=Good

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
```

seeder продуктов `database/seeders/GoodSeeder.php`

```bash
php artisan make:seeder GoodSeeder
```

```php
namespace Database\Seeders;

use App\Models\Good;
use App\Models\ProductFactory;
use Illuminate\Database\Seeder;

class GoodSeeder extends Seeder
{
    public function run()
    {
        \App\Models\Good::factory(100)->create();
    }
}
```

запустите сидер:

```bash
php artisan db:seed --class=GoodSeeder
```




<h2>Вакансия: PHP-разработчик, laravel</h2>

<h2>компании: JeLeApps</h2>

<h3>Развернуть проект</h3>

```php
git clone https://github.com/SlavKoVrn/LaravelRestApi profile
cd profile
composer install
copy .env.example .env
php artisan key:generate
```

<h3>Создать базу данных и привязать к проекту в файле .env</h3>

```php
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=api
DB_USERNAME=root
DB_PASSWORD=password
```

<h3>Разработать метод апи «Регистрация нового юзера» api/registration с параметрами email, password, gender</h3>

<h4>1. добавить в миграцию поле gender файл database/migrations/2014_10_12_000000_create_users_table.php</h4>

```php
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('gender')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }
```

<h4>2. Установить пароль в seeder database/seeders/DatabaseSeeder.php для создания в базе пользователя с известным паролем</h4>

```php
use Illuminate\Support\Facades\Hash;
    public function run()
    {
        \App\Models\User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('123456'),
        ]);
    }
```

<h4>3. выполнить миграцию базы данных</h4>

```php
php artisan migrate --seed
```

<h4>4. создать контроллер для регистрации</h4>

```php
php artisan make:controller Api/AuthController
```

<h4>5. добавить логику выполнения регистрации в файл app/Http/Controllers/Api/AuthController.php</h4>

```php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'gender' => 'nullable|string|in:male,female,other',
        ]);

        $validated['password'] = Hash::make($request->password);

        $user = User::create($validated);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }
}
```

<h4>6. добавить route для контролера регистрации файл routes/api.php</h4>

```php
use App\Http\Controllers\Api\AuthController;
Route::post('/register', [AuthController::class, 'register']);
```

<h4>7. проверка работы api/register тест файл tests/Feature/RegisterTest.php</h4>

```php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_user_can_register()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'gender' => 'male'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'User registered successfully',
            ]);

        // Проверяем, что пользователь создан в БД
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }
}
```

<h4>8. запуск теста проверки работы контроллера регистрации</h4>

```php
php artisan test --filter RegisterTest
```

<h3>Разработать апи метод api/profile для выдачи данных Пользователя</h3>

<h4>1.создать контроллер app/Http/Controllers/Api/ProfileController.php</h4>

```php
php artisan make:controller Api/ProfileController
```

<h4>2.добавить логику в контроллер </h4>

```php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show()
    {
        return response()->json(Auth::user());
    }
}
```

<h4>3. добавить route для контролера получения профиля файл routes/api.php</h4>

```php
use App\Http\Controllers\Api\ProfileController;
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
});
```

<h4>4. проверка работы api/profile тест файл tests/Feature/ProfileTest.php</h4>

```php
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
```

<h4>5. запуск теста проверки работы контроллера получения профиля зарегистрированного пользователя</h4>

```php
php artisan test --filter ProfileTest
```

<h2>REST API</h2>

<h3>AdminLTE</h3>

Step 1: Install Laravel

```php
composer create-project laravel/laravel restapi
```

Step 2: Install jeroennoten/laravel-adminlte

```php
composer require jeroennoten/laravel-adminlte
```

Step 3: Install AdminLTE 3 Theme

```php
php artisan adminlte:install
```

Step 4: Install Laravel UI for Auth Scaffolding

```php
composer require laravel/ui
npm install & npm run dev
php artisan ui bootstrap --auth
php artisan adminlte:install --only=auth_views
```

Step 5: Use AdminLTE Sections

```php
resources/views/home.blade.php
@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <p>Welcome to this beautiful admin panel.</p>
@stop

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script>
        $(function(){
            if (localStorage.getItem('sidebar-collapse') === 'true') {
                $('body').addClass('sidebar-collapse');
            }
            $('a[data-widget="pushmenu"]').click(function(){
                if ($('body').hasClass('sidebar-collapse')) {
                    localStorage.setItem('sidebar-collapse', false);
                } else {
                    localStorage.setItem('sidebar-collapse', true);
                }
            });
        })
    </script>
@stop
```

<h3>Create table News</h3>

create a `News` model

```bash
php artisan make:model News -m
```

This command creates:
- A `News` model (`app/Models/News.php`)
- A migration file for the `news` table (`database/migrations/xxxx_xx_xx_create_news_table.php`)

Edit the migration file to define the structure of the `news` table:

```php
// database/migrations/xxxx_xx_xx_create_news_table.php
    public function up()
    {
        Schema::create('news', function (Blueprint $table) {

            $table->id();
            $table->tinyInteger('active')->default(0)->index(); // 0 = inactive, 1 = active
            $table->timestamp('created_at')->default(\DB::raw('CURRENT_TIMESTAMP'))->index();
            $table->timestamp('updated_at')->default(\DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->index();
            $table->string('title')->index();
            $table->text('content');

        });
    }
```

Run the migration to create the table:

```bash
php artisan migrate
```

`News` model:

```php
// app/Models/News.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content', 'active'];
}
```

<h3>Create API NewsController</h3>

In Laravel 12

```php
php artisan install:api
```

In Laravel, the resource controller pattern is used to automatically create routes for CRUD (Create, Read, Update, Delete) operations

```php
php artisan make:controller NewsController --api
```

route in routes/api.php:

```php
use App\Http\Controllers\NewsController;

Route::apiResource('news', NewsController::class);
```

Below are the definitions of the following routes:

```php
GET /api/news:  Fetch all news
GET /api/news/{id}:  Fetch a specific news
POST /api/news:  Create a new news
PUT/PATCH /api/news/{id}:  Update an existing news
DELETE /api/news/{id}:  Delete a news
```

<h3>Test NewsController</h3>

```php
php vendor/bin/phpunit tests/Feature/ApiTest.php
```

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 1500 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
