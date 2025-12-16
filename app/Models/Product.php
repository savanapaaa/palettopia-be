<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'brand',
        'category',
        'price',
        'stock',
        'image_url',
        'palette_category',
        'description'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function analysisHistories() {
        return $this->belongsToMany(AnalysisHistory::class, 'detail_rekomendasi');
    }

    public function palettes() {
        return $this->hasMany(ProductPalette::class);
    }
}

