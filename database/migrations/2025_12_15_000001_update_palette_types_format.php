<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Update existing palette categories to new format:
     * - Spring Warm -> spring bright
     * - Summer Cool -> summer cool
     * - Autumn Warm -> autumn warm
     * - Winter Cool -> winter clear
     */
    public function up(): void
    {
        // Update products table
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'palette_category')) {
            DB::table('products')
                ->where('palette_category', 'LIKE', '%Spring%')
                ->orWhere('palette_category', 'LIKE', '%spring%')
                ->update(['palette_category' => 'spring bright']);
            
            DB::table('products')
                ->where('palette_category', 'LIKE', '%Summer%')
                ->orWhere('palette_category', 'LIKE', '%summer%')
                ->update(['palette_category' => 'summer cool']);
            
            DB::table('products')
                ->where('palette_category', 'LIKE', '%Autumn%')
                ->orWhere('palette_category', 'LIKE', '%autumn%')
                ->update(['palette_category' => 'autumn warm']);
            
            DB::table('products')
                ->where('palette_category', 'LIKE', '%Winter%')
                ->orWhere('palette_category', 'LIKE', '%winter%')
                ->update(['palette_category' => 'winter clear']);
        }

        // Update analysis_histories table
        if (Schema::hasTable('analysis_histories') && Schema::hasColumn('analysis_histories', 'result_palette')) {
            DB::table('analysis_histories')
                ->where('result_palette', 'LIKE', '%Spring%')
                ->orWhere('result_palette', 'LIKE', '%spring%')
                ->update(['result_palette' => 'spring bright']);
            
            DB::table('analysis_histories')
                ->where('result_palette', 'LIKE', '%Summer%')
                ->orWhere('result_palette', 'LIKE', '%summer%')
                ->update(['result_palette' => 'summer cool']);
            
            DB::table('analysis_histories')
                ->where('result_palette', 'LIKE', '%Autumn%')
                ->orWhere('result_palette', 'LIKE', '%autumn%')
                ->update(['result_palette' => 'autumn warm']);
            
            DB::table('analysis_histories')
                ->where('result_palette', 'LIKE', '%Winter%')
                ->orWhere('result_palette', 'LIKE', '%winter%')
                ->update(['result_palette' => 'winter clear']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to old format if needed
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'palette_category')) {
            DB::table('products')->where('palette_category', 'spring bright')->update(['palette_category' => 'Spring Warm']);
            DB::table('products')->where('palette_category', 'summer cool')->update(['palette_category' => 'Summer Cool']);
            DB::table('products')->where('palette_category', 'autumn warm')->update(['palette_category' => 'Autumn Warm']);
            DB::table('products')->where('palette_category', 'winter clear')->update(['palette_category' => 'Winter Cool']);
        }

        if (Schema::hasTable('analysis_histories') && Schema::hasColumn('analysis_histories', 'result_palette')) {
            DB::table('analysis_histories')->where('result_palette', 'spring bright')->update(['result_palette' => 'Spring Warm']);
            DB::table('analysis_histories')->where('result_palette', 'summer cool')->update(['result_palette' => 'Summer Cool']);
            DB::table('analysis_histories')->where('result_palette', 'autumn warm')->update(['result_palette' => 'Autumn Warm']);
            DB::table('analysis_histories')->where('result_palette', 'winter clear')->update(['result_palette' => 'Winter Cool']);
        }
    }
};
