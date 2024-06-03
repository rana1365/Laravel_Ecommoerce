<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create multiple users
        // \App\Models\User::factory(10)->create();

        // Create an admin user if it doesn't exist
        $adminEmail = 'admin@email.com';
        if (!User::where('email', $adminEmail)->exists()) {
            User::create([
                'name' => 'Admin',
                'email' => $adminEmail,
                'password' => Hash::make('admin'),
                'role' => 2
            ]);
        }

        // Create categories
        // \App\Models\Category::factory(10)->create();

        // Create products
        \App\Models\Product::factory(30)->create();
    }
}
