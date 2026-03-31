<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vendor;
use Illuminate\Support\Str;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        Vendor::create([
            'name' => 'Apple Store',
            'email' => 'apple@test.com',
            'slug' => Str::slug('Apple Store'),
            'description' => 'Apple official products',
            'is_active' => true
        ]);

        Vendor::create([
            'name' => 'Samsung Store',
            'email' => 'samsung@test.com',
            'slug' => Str::slug('Samsung Store'),
            'description' => 'Samsung electronics',
            'is_active' => true
        ]);
    }
}
