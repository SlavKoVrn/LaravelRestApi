<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('123456'),
        ]);
        \App\Models\User::factory(10)->create();
        \App\Models\News::factory(100)->create();
        \App\Models\GoogleLink::factory()->create([
            'database_table' => 'google_links',
            'google_link' => 'https://docs.google.com/spreadsheets/d/13skwS6srZ0Z9YBWXyQUn6ltnK1P0qPbls4H-LqS7bRw/edit',
            'google_config' => '{}',
        ]);
    }
}
