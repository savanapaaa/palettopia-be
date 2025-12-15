<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $url;
    protected string $key;

    public function __construct()
    {
        $this->url = config('services.gemini.url') ?: env('GEMINI_API_URL', '');
        $this->key = config('services.gemini.key') ?: env('GEMINI_API_KEY', '');
    }

    /**
     * Call the LLM provider with prompt and try parse JSON response.
     * IMPORTANT: adapt request structure to your LLM provider's API.
     */
    public function callLLM(string $prompt): ?array
    {
        try {
            $resp = Http::withOptions(['verify' => false])->post($this->url . '?key=' . $this->key, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 800,
                ]
            ]);

            if (! $resp->ok()) {
                Log::error('LLM request failed', ['status' => $resp->status(), 'body' => $resp->body()]);
                return null;
            }

            $body = $resp->json();

            // Extract text from Gemini response format
            $text = $body['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (! $text) {
                Log::warning('LLM returned unexpected shape', ['body' => $body]);
                return null;
            }

            // Try JSON decode the text
            $parsed = json_decode($text, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $parsed;
            }

            // fallback: extract JSON substring
            if (preg_match('/\{.*\}/s', $text, $matches)) {
                $maybe = json_decode($matches[0], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $maybe;
                }
            }

            return ['raw_text' => $text];
        } catch (\Throwable $e) {
            Log::error('LLM call error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return null;
        }
    }

    /**
     * Extract dominant colors from image using PHP GD
     * @param string $imageUrl URL or path of the uploaded image
     * @return array|null Returns array of HEX colors
     */
    private function extractColorsFromImage(string $imageUrl): ?array
    {
        try {
            // Read image content
            if (str_starts_with($imageUrl, '/storage/')) {
                $path = str_replace('/storage/', '', $imageUrl);
                $publicPath = public_path('storage/' . $path);
                if (!file_exists($publicPath)) {
                    return null;
                }
                $imagePath = $publicPath;
            } else {
                return null;
            }

            // Create image resource from file
            $imageInfo = getimagesize($imagePath);
            if (!$imageInfo) {
                return null;
            }

            $mimeType = $imageInfo['mime'];
            
            // Check if GD extension is available
            if (!function_exists('imagecreatefromjpeg')) {
                Log::warning('GD extension not available, cannot extract colors');
                return null;
            }
            
            $image = match($mimeType) {
                'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($imagePath),
                'image/png' => @imagecreatefrompng($imagePath),
                'image/gif' => @imagecreatefromgif($imagePath),
                default => null
            };

            if (!$image) {
                Log::warning('Failed to create image resource', ['mimeType' => $mimeType]);
                return null;
            }

            // Resize image for faster processing
            $width = imagesx($image);
            $height = imagesy($image);
            $newWidth = 100;
            $newHeight = intval($height * ($newWidth / $width));
            
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Sample colors from center area (where face usually is)
            $colors = [];
            $centerX = intval($newWidth / 2);
            $centerY = intval($newHeight / 2);
            $sampleRadius = intval(min($newWidth, $newHeight) / 4);

            // Sample multiple points in center area
            for ($i = 0; $i < 20; $i++) {
                $x = $centerX + rand(-$sampleRadius, $sampleRadius);
                $y = $centerY + rand(-$sampleRadius, $sampleRadius);
                
                $x = max(0, min($newWidth - 1, $x));
                $y = max(0, min($newHeight - 1, $y));
                
                $rgb = imagecolorat($resized, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                
                // Filter out very dark or very light pixels (likely not skin)
                $brightness = ($r + $g + $b) / 3;
                if ($brightness > 30 && $brightness < 250) {
                    $colors[] = sprintf('#%02X%02X%02X', $r, $g, $b);
                }
            }

            imagedestroy($image);
            imagedestroy($resized);

            // Return unique colors, limit to 8
            return array_values(array_unique($colors));

        } catch (\Throwable $e) {
            Log::error('Color extraction error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Analyze image and extract skin tone colors using Gemini Vision API
     * @param string $imageUrl URL or path of the uploaded image
     * @return array|null Returns array with 'colors' and 'analysis' or null on error
     */
    public function analyzeImageColors(string $imageUrl): ?array
    {
        try {
            Log::info('analyzeImageColors called', ['imageUrl' => $imageUrl]);
            
            // Extract colors from image using PHP GD
            $extractedColors = $this->extractColorsFromImage($imageUrl);
            if ($extractedColors && count($extractedColors) >= 3) {
                Log::info('Colors extracted from image', ['colors' => $extractedColors]);
                return ['colors' => array_slice($extractedColors, 0, 8)];
            }
            
            // If extraction failed, continue with Vision API...
            
            // Handle both local storage paths and URLs
            if (str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://')) {
                // Remote URL - download it
                Log::info('Reading image from URL');
                $imageContent = @file_get_contents($imageUrl);
            } elseif (str_starts_with($imageUrl, '/storage/')) {
                // Local storage path - read from public disk
                // Remove /storage/ prefix to get actual path in storage/app/public
                $path = str_replace('/storage/', '', $imageUrl);
                Log::info('Reading image from storage', ['path' => $path]);
                
                // Check if file exists
                if (!\Storage::disk('public')->exists($path)) {
                    // Try alternative: check if storage link exists and read via public path
                    $publicPath = public_path('storage/' . $path);
                    if (file_exists($publicPath)) {
                        Log::info('Reading from public/storage symlink', ['publicPath' => $publicPath]);
                        $imageContent = file_get_contents($publicPath);
                    } else {
                        Log::error('File does not exist in storage', [
                            'path' => $path,
                            'publicPath' => $publicPath,
                            'storagePath' => storage_path('app/public/' . $path)
                        ]);
                        return null;
                    }
                } else {
                    $imageContent = \Storage::disk('public')->get($path);
                }
            } else {
                // Try as absolute path
                $fullPath = public_path($imageUrl);
                Log::info('Reading image from public path', ['fullPath' => $fullPath]);
                
                if (!file_exists($fullPath)) {
                    Log::error('File does not exist', ['fullPath' => $fullPath]);
                    return null;
                }
                
                $imageContent = file_get_contents($fullPath);
            }

            if ($imageContent === false || empty($imageContent)) {
                Log::error('Failed to read image content', ['url' => $imageUrl]);
                return null;
            }
            
            Log::info('Image read successfully', ['size' => strlen($imageContent)]);

            $base64Image = base64_encode($imageContent);
            
            // Detect mime type
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($imageContent);

            $prompt = <<<PROMPT
Analyze this person's face photo and extract the dominant skin tone colors.

Your task:
1. Identify the person's skin undertone (warm, cool, or neutral)
2. Extract 5-8 HEX color codes that represent their skin tones (from lightest to darkest areas)
3. Determine their seasonal color palette type from these exact options:
   - "winter clear" (for cool undertones with high contrast and clear, icy colors)
   - "summer cool" (for cool undertones with soft, muted, dusty colors)
   - "spring bright" (for warm undertones with bright, clear, fresh colors)
   - "autumn warm" (for warm undertones with rich, earthy, muted colors)

Based on the palette type, assign appropriate HEX color codes:
- winter clear: Use colors like #E8F1F5, #B4D4E1, #7FB3D5, #5499C7, #2980B9, #1F618D, #1A5276, #154360
- summer cool: Use colors like #85E3FF, #ACE7FF, #A7C7E7, #B4E7CE, #95E1D3, #7FCDCD, #82CAFF, #A0CFEC
- spring bright: Use colors like #FFB5E8, #FF9CEE, #FFCCF9, #FCC2FF, #F6A6FF, #82BDFF, #C5A3FF, #D5AAFF
- autumn warm: Use colors like #E07A5F, #F2CC8F, #81B29A, #C1666B, #D4A373, #3D5A80, #774936, #F4F1DE

Return ONLY valid JSON with this structure:
{
  "colors": ["#HEX1", "#HEX2", "#HEX3", "#HEX4", "#HEX5", "#HEX6", "#HEX7", "#HEX8"],
  "undertone": "warm/cool/neutral",
  "palette_name": "winter clear/summer cool/spring bright/autumn warm",
  "explanation": "Brief explanation why this palette suits them"
}

Important: 
- Use EXACT palette names: "winter clear", "summer cool", "spring bright", or "autumn warm"
- Return ONLY the JSON, no additional text.
PROMPT;

            $resp = Http::withOptions(['verify' => false])->post($this->url . '?key=' . $this->key, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inline_data' => [
                                    'mime_type' => $mimeType,
                                    'data' => $base64Image
                                ]
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.5,
                    'maxOutputTokens' => 1000,
                ]
            ]);

            if (! $resp->ok()) {
                Log::error('Vision API request failed', [
                    'status' => $resp->status(), 
                    'body' => $resp->body(),
                    'url' => $this->url,
                    'imageSize' => strlen($base64Image)
                ]);
                return null;
            }

            $body = $resp->json();
            $text = $body['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (! $text) {
                Log::warning('Vision API returned unexpected shape', ['body' => $body]);
                return null;
            }

            // Try JSON decode
            $parsed = json_decode($text, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $parsed;
            }

            // Fallback: extract JSON substring
            if (preg_match('/\{.*\}/s', $text, $matches)) {
                $maybe = json_decode($matches[0], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $maybe;
                }
            }

            Log::warning('Failed to parse Vision API JSON response', ['text' => $text]);
            return null;

        } catch (\Throwable $e) {
            Log::error('Vision API error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return null;
        }
    }
}
