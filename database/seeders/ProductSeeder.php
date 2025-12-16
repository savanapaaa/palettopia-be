<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductPalette;
use App\Models\User;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user
        $admin = User::where('role', 'admin')->first();

        // Sample products with multiple palettes
        $products = [
            [
                'name' => 'Kemeja Putih Elegan',
                'category' => 'Atasan',
                'price' => 150000,
                'stock' => 25,
                'description' => 'Kemeja putih dengan potongan modern cocok untuk palette cool',
                'image_url' => null,
                'palettes' => ['winter clear', 'summer cool'],
            ],
            [
                'name' => 'Blouse Pink Pastel',
                'category' => 'Atasan',
                'price' => 120000,
                'stock' => 30,
                'description' => 'Blouse pink lembut perfect untuk spring bright',
                'image_url' => null,
                'palettes' => ['spring bright', 'summer cool'],
            ],
            [
                'name' => 'Dress Navy Blue',
                'category' => 'Dress',
                'price' => 250000,
                'stock' => 15,
                'description' => 'Dress navy elegant untuk acara formal',
                'image_url' => null,
                'palettes' => ['winter clear'],
            ],
            [
                'name' => 'Celana Cream Casual',
                'category' => 'Bawahan',
                'price' => 180000,
                'stock' => 20,
                'description' => 'Celana cream nyaman untuk daily wear',
                'image_url' => null,
                'palettes' => ['autumn warm', 'spring bright'],
            ],
            [
                'name' => 'Cardigan Coklat Moka',
                'category' => 'Outerwear',
                'price' => 200000,
                'stock' => 12,
                'description' => 'Cardigan hangat dengan warna earthy',
                'image_url' => null,
                'palettes' => ['autumn warm'],
            ],
            [
                'name' => 'Scarf Lavender',
                'category' => 'Aksesoris',
                'price' => 75000,
                'stock' => 50,
                'description' => 'Scarf lavender soft untuk melengkapi outfit',
                'image_url' => null,
                'palettes' => ['spring bright', 'summer cool'],
            ],
            [
                'name' => 'Jaket Denim Biru',
                'category' => 'Outerwear',
                'price' => 350000,
                'stock' => 10,
                'description' => 'Jaket denim klasik cocok untuk berbagai palette',
                'image_url' => null,
                'palettes' => ['winter clear', 'summer cool', 'autumn warm'],
            ],
            [
                'name' => 'Rok Midi Terracotta',
                'category' => 'Bawahan',
                'price' => 165000,
                'stock' => 18,
                'description' => 'Rok midi dengan warna terracotta warm',
                'image_url' => null,
                'palettes' => ['autumn warm'],
            ],
        ];

        foreach ($products as $productData) {
            $palettes = $productData['palettes'];
            unset($productData['palettes']);

            $product = Product::create(array_merge($productData, [
                'user_id' => $admin->id,
            ]));

            // Create palette associations
            foreach ($palettes as $paletteName) {
                ProductPalette::create([
                    'product_id' => $product->id,
                    'palette_name' => $paletteName,
                ]);
            }
        }
    }
}
