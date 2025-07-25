<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Mark Jason Espelita',
            'email' => 'markjasonespelita@gmail.com',
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'role' => 'admin',
        ]);
    }
}
