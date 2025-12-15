<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Constants\PaletteTypes;
use Illuminate\Http\Request;

class PaletteController extends Controller
{
    /**
     * GET /api/palettes
     * Get all available palette types with details
     */
    public function index()
    {
        $palettes = [];
        
        foreach (PaletteTypes::all() as $palette) {
            $palettes[] = [
                'name' => $palette,
                'undertone' => PaletteTypes::getUndertone($palette),
                'description' => PaletteTypes::getDescription($palette),
                'colors' => PaletteTypes::getColors($palette),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $palettes,
        ]);
    }

    /**
     * GET /api/palettes/{palette}
     * Get specific palette details
     */
    public function show($palette)
    {
        // Convert to lowercase for consistency
        $palette = strtolower($palette);

        if (!PaletteTypes::isValid($palette)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid palette type',
                'valid_palettes' => PaletteTypes::all(),
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'name' => $palette,
                'undertone' => PaletteTypes::getUndertone($palette),
                'description' => PaletteTypes::getDescription($palette),
                'colors' => PaletteTypes::getColors($palette),
            ],
        ]);
    }
}
