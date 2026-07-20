<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['Moisture Repair Shampoo', 24.00, 12.00, 40],
            ['Moisture Repair Conditioner', 24.00, 12.00, 40],
            ['Argan Hair Serum', 32.00, 15.00, 25],
            ['Gel Nail Polish - Ruby Red', 14.00, 6.00, 60],
            ['Hydrating Face Cream', 45.00, 20.00, 18],
            ['Body Lotion - Lavender', 22.00, 9.00, 30],
            ['Heat Protectant Spray', 26.00, 11.00, 8],
        ];

        foreach ($products as [$name, $price, $cost, $stock]) {
            Product::firstOrCreate(
                ['sku' => strtoupper('SKU-'.str_replace(' ', '-', $name))],
                [
                    'name' => $name,
                    'description' => "{$name}, available for retail purchase.",
                    'price' => $price,
                    'cost' => $cost,
                    'stock' => $stock,
                    'low_stock_threshold' => 10,
                    'is_active' => true,
                ]
            );
        }
    }
}
