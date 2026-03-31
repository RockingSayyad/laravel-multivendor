<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::create([
            'name' => 'iPhone 15',
            'price' => 80000,
            'stock' => 10,
            'vendor_id' => 1,
            'is_active' => true
        ]);

        Product::create([
            'name' => 'Samsung S23',
            'price' => 70000,
            'stock' => 15,
            'vendor_id' => 2,
            'is_active' => true
        ]);
    }
}

