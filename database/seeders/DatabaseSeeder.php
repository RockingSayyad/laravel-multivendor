<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Test User
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password') // IMPORTANT
        ]);

        // Call other seeders
        $this->call([
            VendorSeeder::class,
            ProductSeeder::class,
        ]);
    }
}