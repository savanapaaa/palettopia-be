<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\AnalysisHistory;
use App\Constants\PaletteTypes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Check if user is admin
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Admin access only.',
                ], 403);
            }
            return $next($request);
        });
    }

    /**
     * GET /api/admin/statistics
     * Dashboard statistics
     */
    public function statistics()
    {
        try {
            $stats = [
                'total_users' => User::where('role', 'user')->count(),
                'total_admins' => User::where('role', 'admin')->count(),
                'total_products' => Product::count(),
                'total_analyses' => AnalysisHistory::count(),
                'products_by_palette' => [
                    'winter_clear' => Product::where('palette_category', 'winter clear')->count(),
                    'summer_cool' => Product::where('palette_category', 'summer cool')->count(),
                    'spring_bright' => Product::where('palette_category', 'spring bright')->count(),
                    'autumn_warm' => Product::where('palette_category', 'autumn warm')->count(),
                ],
                'recent_analyses' => AnalysisHistory::with('user:id,name,email')
                    ->orderByDesc('created_at')
                    ->limit(5)
                    ->get(),
                'analyses_by_palette' => [
                    'winter_clear' => AnalysisHistory::where('result_palette', 'winter clear')->count(),
                    'summer_cool' => AnalysisHistory::where('result_palette', 'summer cool')->count(),
                    'spring_bright' => AnalysisHistory::where('result_palette', 'spring bright')->count(),
                    'autumn_warm' => AnalysisHistory::where('result_palette', 'autumn warm')->count(),
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            Log::error('Admin statistics error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics',
            ], 500);
        }
    }

    /**
     * GET /api/admin/products
     * List all products (admin view with pagination & stats)
     */
    public function products(Request $request)
    {
        try {
            $query = Product::query();

            // Filter by palette category
            if ($request->has('palette_category')) {
                $query->where('palette_category', $request->palette_category);
            }

            // Search by name
            if ($request->has('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            $perPage = $request->get('per_page', 10);
            $products = $query->orderByDesc('created_at')->paginate($perPage);

            // Transform products to include palettes array
            $products->getCollection()->transform(function ($product) {
                $palettes = [];
                if ($product->palette_category) {
                    $palettes[] = [
                        'id' => null, // Temporary, nanti pakai pivot table
                        'palette_name' => $product->palette_category
                    ];
                }
                
                $product->palettes = $palettes;
                return $product;
            });

            // Calculate stats
            $stats = [
                'total_products' => Product::count(),
                'total_stock' => Product::sum('stock'),
                'total_categories' => Product::distinct('category')->count('category'),
                'total_palettes' => Product::distinct('palette_category')->count('palette_category'),
            ];

            return response()->json([
                'success' => true,
                'data' => $products,
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            Log::error('Admin products list error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch products',
            ], 500);
        }
    }

    /**
     * POST /api/admin/products
     * Create new product
     */
    public function storeProduct(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'brand' => 'nullable|string|max:255',
                'category' => 'required|string|max:100',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'description' => 'nullable|string',
                'palettes' => 'required|array|min:1',
                'palettes.*' => 'required|' . PaletteTypes::validationRule(),
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            ]);

            // Handle image upload if provided
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $filename = time() . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('products', $filename, 'public');
                $validated['image_url'] = '/storage/' . $path;
            }

            $validated['user_id'] = auth()->id();
            $validated['palette_category'] = $validated['palettes'][0]; // Fallback untuk kolom lama

            // Extract palettes array before creating product
            $palettes = $validated['palettes'];
            unset($validated['palettes']);

            $product = Product::create($validated);

            // Insert palettes ke product_palettes table
            foreach ($palettes as $paletteName) {
                $product->palettes()->create(['palette_name' => $paletteName]);
            }

            // Load palettes relationship untuk response
            $product->load('palettes');

            // Transform palettes untuk response
            $product->palettes = $product->palettes->map(function($palette) {
                return [
                    'id' => $palette->id,
                    'palette_name' => $palette->palette_name
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan',
                'data' => $product,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Admin create product error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product',
            ], 500);
        }
    }

    /**
     * PUT /api/admin/products/{id}
     * Update product
     */
    public function updateProduct(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'brand' => 'nullable|string|max:255',
                'category' => 'sometimes|required|string|max:100',
                'price' => 'sometimes|required|numeric|min:0',
                'stock' => 'sometimes|required|integer|min:0',
                'description' => 'nullable|string',
                'palettes' => 'sometimes|required|array|min:1',
                'palettes.*' => 'required|' . PaletteTypes::validationRule(),
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            ]);

            // Handle image upload if provided
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($product->image_url) {
                    $oldPath = str_replace('/storage/', '', $product->image_url);
                    Storage::disk('public')->delete($oldPath);
                }

                $image = $request->file('image');
                $filename = time() . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('products', $filename, 'public');
                $validated['image_url'] = '/storage/' . $path;
            }

            // Update palettes jika dikirim
            if (isset($validated['palettes'])) {
                $palettes = $validated['palettes'];
                $validated['palette_category'] = $palettes[0]; // Update fallback column
                unset($validated['palettes']);

                // Hapus palettes lama
                $product->palettes()->delete();

                // Insert palettes baru
                foreach ($palettes as $paletteName) {
                    $product->palettes()->create(['palette_name' => $paletteName]);
                }
            }

            $product->update($validated);

            // Load palettes untuk response
            $product->load('palettes');
            $product->palettes = $product->palettes->map(function($palette) {
                return [
                    'id' => $palette->id,
                    'palette_name' => $palette->palette_name
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil diperbarui',
                'data' => $product->fresh(['palettes']),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Admin update product error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product',
            ], 500);
        }
    }

    /**
     * DELETE /api/admin/products/{id}
     * Delete product
     */
    public function destroyProduct($id)
    {
        try {
            $product = Product::findOrFail($id);

            // Delete image if exists
            if ($product->image_url) {
                $path = str_replace('/storage/', '', $product->image_url);
                Storage::disk('public')->delete($path);
            }

            // Delete palettes (cascade akan handle otomatis karena onDelete('cascade'))
            $product->palettes()->delete();

            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil dihapus',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Admin delete product error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product',
            ], 500);
        }
    }

    /**
     * GET /api/admin/analyses
     * View all user analyses (admin)
     */
    public function analyses(Request $request)
    {
        try {
            $query = AnalysisHistory::with('user:id,name,email');

            // Filter by palette
            if ($request->has('palette')) {
                $query->where('result_palette', $request->palette);
            }

            // Filter by user
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            // Search by user email/name
            if ($request->has('search')) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            }

            // Date filter
            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $perPage = $request->get('per_page', 20);
            $analyses = $query->orderByDesc('created_at')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $analyses,
            ]);
        } catch (\Exception $e) {
            Log::error('Admin analyses list error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch analyses',
            ], 500);
        }
    }
}
