<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaletteProductSeeder extends Seeder
{
    /**
     * Seed sample products for each palette type with accurate color codes.
     */
    public function run(): void
    {
        // Create a test user if not exists
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'test@palettopia.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password123'),
                'role' => 'user',
            ]
        );

        $products = [
            // Winter Clear Products
            [
                'name' => 'Winter Clear Lipstick #5499C7 by ByNeer - Rp 85.000',
                'image_url' => null,
                'palette_category' => 'winter clear',
                'description' => 'Clear blue toned lipstick for winter clear palette. Brand: ByNeer, Price: Rp 85.000',
                'user_id' => $user->id,
            ],
            [
                'name' => 'Winter Clear Blush #E07A5F by ByNeer - Rp 95.000',
                'image_url' => null,
                'palette_category' => 'winter clear',
                'description' => 'Coral pink blush for high contrast winter clear look. Brand: ByNeer, Price: Rp 95.000',
                'user_id' => $user->id,
            ],
            [
                'name' => 'Winter Clear Eyeshadow #82CAFF by ByNeer - Rp 120.000',
                'image_url' => null,
                'palette_category' => 'winter clear',
                'description' => 'Ice blue eyeshadow palette for winter clear. Brand: ByNeer, Price: Rp 120.000',
                'user_id' => $user->id,
            ],

            // Summer Cool Products
            [
                'name' => 'Summer Cool Lipstick #B4E7CE by ByNeer - Rp 85.000',
                'image_url' => null,
                'palette_category' => 'summer cool',
                'description' => 'Mint green toned lipstick for summer cool palette. Brand: ByNeer, Price: Rp 85.000',
                'user_id' => $user->id,
            ],
            [
                'name' => 'Summer Cool Blush #E5D1D3 by ByNeer - Rp 95.000',
                'image_url' => null,
                'palette_category' => 'summer cool',
                'description' => 'Dusty pink blush for soft summer cool look. Brand: ByNeer, Price: Rp 95.000',
                'user_id' => $user->id,
            ],
            [
                'name' => 'Summer Cool Eyeshadow #7FCDCD by ByNeer - Rp 120.000',
                'image_url' => null,
                'palette_category' => 'summer cool',
                'description' => 'Aqua eyeshadow palette for summer cool. Brand: ByNeer, Price: Rp 120.000',
                'user_id' => $user->id,
            ],

            // Spring Bright Products
            [
                'name' => 'Spring Bright Lipstick #FFCCF9 by ByNeer - Rp 85.000',
                'image_url' => null,
                'palette_category' => 'spring bright',
                'description' => 'Light pink lipstick for spring bright palette. Brand: ByNeer, Price: Rp 85.000',
                'user_id' => $user->id,
            ],
            [
                'name' => 'Spring Bright Blush #FFC2FF by ByNeer - Rp 95.000',
                'image_url' => null,
                'palette_category' => 'spring bright',
                'description' => 'Bright pink blush for vibrant spring bright look. Brand: ByNeer, Price: Rp 95.000',
                'user_id' => $user->id,
            ],
            [
                'name' => 'Spring Bright Eyeshadow #D5AAFF by ByNeer - Rp 120.000',
                'image_url' => null,
                'palette_category' => 'spring bright',
                'description' => 'Light purple eyeshadow palette for spring bright. Brand: ByNeer, Price: Rp 120.000',
                'user_id' => $user->id,
            ],

            // Autumn Warm Products
            [
                'name' => 'Autumn Warm Lipstick #E07A5F by ByNeer - Rp 85.000',
                'image_url' => null,
                'palette_category' => 'autumn warm',
                'description' => 'Terracotta lipstick for autumn warm palette. Brand: ByNeer, Price: Rp 85.000',
                'user_id' => $user->id,
            ],
            [
                'name' => 'Autumn Warm Blush #C1666B by ByNeer - Rp 95.000',
                'image_url' => null,
                'palette_category' => 'autumn warm',
                'description' => 'Dusty rose blush for rich autumn warm look. Brand: ByNeer, Price: Rp 95.000',
                'user_id' => $user->id,
            ],
            [
                'name' => 'Autumn Warm Eyeshadow #81B29A by ByNeer - Rp 120.000',
                'image_url' => null,
                'palette_category' => 'autumn warm',
                'description' => 'Sage green eyeshadow palette for autumn warm. Brand: ByNeer, Price: Rp 120.000',
                'user_id' => $user->id,
            ],
        ];

        foreach ($products as $product) {
            DB::table('products')->insert(array_merge($product, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Sample palette products seeded successfully!');
    }
}
