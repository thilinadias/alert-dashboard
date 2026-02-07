<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
        ]);

        $user = \App\Models\User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('admin');

        $nocUser = \App\Models\User::factory()->create([
            'name' => 'NOC Analyst',
            'email' => 'analyst@example.com',
            'password' => bcrypt('password'),
        ]);
        $nocUser->assignRole('noc_analyst');
    }
}
