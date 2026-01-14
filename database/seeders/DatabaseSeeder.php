<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Fajar Ghifari',
            'username' => 'admin',
            'email' => 'admin@admin.com',
        ]);

        $this->call([
            LocationSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            ConsumableStockSeeder::class,
            AssetSeeder::class,
        ]);
    }
}
