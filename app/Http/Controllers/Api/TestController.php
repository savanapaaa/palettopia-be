<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Test endpoint untuk debug
     */
    public function testAnalysis(Request $request)
    {
        $imageUrl = $request->input('image_url', '/storage/analyses/test.jpg');
        
        Log::info('TEST: Starting analysis', ['url' => $imageUrl]);
        
        // Test color extraction
        $colors = $this->geminiService->analyzeImageColors($imageUrl);
        
        Log::info('TEST: Analysis result', ['result' => $colors]);
        
        return response()->json([
            'success' => true,
            'message' => 'Test completed',
            'input' => $imageUrl,
            'result' => $colors,
            'gemini_url' => config('services.gemini.url'),
            'has_api_key' => !empty(config('services.gemini.key')),
        ]);
    }
}
