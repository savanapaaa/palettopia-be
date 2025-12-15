<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnalysisHistory;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AnalysisController extends Controller
{
    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * POST /api/analysis
     * Analyze uploaded image for skin tone and palette
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'image_url' => 'required|string',
        ]);

        try {
            $imageUrl = $validated['image_url'];
            
            // Analyze image using Gemini Vision API
            $analysis = $this->geminiService->analyzeImageColors($imageUrl);
            
            if (!$analysis || !isset($analysis['colors']) || empty($analysis['colors'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to analyze image. Please make sure the image contains a clear face photo.',
                ], 400);
            }

            // Extract data from analysis
            $paletteName = $analysis['palette_name'] ?? null;
            $undertone = $analysis['undertone'] ?? 'neutral';
            $explanation = $analysis['explanation'] ?? '';
            
            // If Gemini AI failed to determine palette, use color-based logic as fallback
            if (!$paletteName || !in_array($paletteName, ['winter clear', 'summer cool', 'spring bright', 'autumn warm'])) {
                // Simple fallback logic based on color warmth
                $aiColors = $analysis['colors'] ?? [];
                $paletteName = $this->determinePaletteFromColors($aiColors);
                $explanation = 'Palette determined based on your skin tone colors. For more accurate results, try uploading a clearer photo.';
            }
            
            // ALWAYS use fixed colors from PaletteTypes (matching Google Drive products)
            $colors = \App\Constants\PaletteTypes::getColors($paletteName);

            // Save to database
            $history = AnalysisHistory::create([
                'user_id' => auth()->id(),
                'result_palette' => $paletteName,
                'colors' => $colors,
                'image_url' => $imageUrl,
                'ai_result' => $analysis,
                'input_data' => [
                    'undertone' => $undertone,
                ],
                'notes' => $explanation,
            ]);

            // Get product recommendations for this palette
            $recommendations = \App\Models\Product::where('palette_category', $paletteName)
                ->orderByDesc('created_at')
                ->limit(8)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $history->id,
                    'palette_name' => $paletteName,
                    'colors' => $colors,
                    'undertone' => $undertone,
                    'explanation' => $explanation,
                    'created_at' => $history->created_at,
                    'recommendations' => $recommendations,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Analysis error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during analysis',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/history
     * Get user's analysis history
     */
    public function index(Request $request)
    {
        $histories = AnalysisHistory::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $histories,
        ]);
    }

    /**
     * DELETE /api/history/{id}
     * Delete a history record
     */
    public function destroy($id)
    {
        $history = AnalysisHistory::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$history) {
            return response()->json([
                'success' => false,
                'message' => 'History not found',
            ], 404);
        }

        $history->delete();

        return response()->json([
            'success' => true,
            'message' => 'History deleted successfully',
        ]);
    }

    /**
     * Determine palette type from color array (fallback method)
     * 
     * @param array $colors Array of HEX color codes
     * @return string Palette name
     */
    private function determinePaletteFromColors(array $colors): string
    {
        if (empty($colors)) {
            return 'autumn warm'; // default fallback
        }
        
        // Calculate average warmth and brightness
        $totalWarmth = 0;
        $totalBrightness = 0;
        $count = 0;
        
        foreach ($colors as $color) {
            $hex = ltrim($color, '#');
            if (strlen($hex) !== 6) continue;
            
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            
            // Warmth: red/yellow bias vs blue bias
            $warmth = ($r + $g) - ($b * 2);
            $brightness = ($r + $g + $b) / 3;
            
            $totalWarmth += $warmth;
            $totalBrightness += $brightness;
            $count++;
        }
        
        if ($count === 0) {
            return 'autumn warm';
        }
        
        $avgWarmth = $totalWarmth / $count;
        $avgBrightness = $totalBrightness / $count;
        
        // Determine palette based on warmth and brightness
        if ($avgWarmth > 0) {
            // Warm undertones
            return $avgBrightness > 160 ? 'spring bright' : 'autumn warm';
        } else {
            // Cool undertones
            return $avgBrightness > 160 ? 'winter clear' : 'summer cool';
        }
    }

    /**
     * GET /api/recommendation
     * Get product recommendations based on user's palette
     */
    public function recommend(Request $request)
    {
        // Get user's latest analysis
        $latestAnalysis = AnalysisHistory::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->first();

        if (!$latestAnalysis) {
            return response()->json([
                'success' => false,
                'message' => 'No analysis found. Please analyze your skin tone first.',
            ], 404);
        }

        $palette = $latestAnalysis->result_palette;

        return response()->json([
            'success' => true,
            'data' => [
                'palette' => $palette,
                'colors' => $latestAnalysis->colors,
                'message' => 'Use this palette to search for products',
            ],
        ]);
    }
}
