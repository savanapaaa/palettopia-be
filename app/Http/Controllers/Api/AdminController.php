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
     * List all products (admin view with pagination)
     */
    public function products(Request $request)
    {
        try {
            $query = Product::with('user:id,name,email');

            // Filter by palette category
            if ($request->has('palette_category')) {
                $query->where('palette_category', $request->palette_category);
            }

            // Search by name
            if ($request->has('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            $perPage = $request->get('per_page', 15);
            $products = $query->orderByDesc('created_at')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $products,
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
                'description' => 'nullable|string',
                'palette_category' => 'required|' . PaletteTypes::validationRule(),
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
                'image_url' => 'nullable|string',
            ]);

            // Handle image upload if provided
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $filename = uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('products', $filename, 'public');
                $validated['image_url'] = '/storage/' . $path;
            }

            $validated['user_id'] = auth()->id();

            $product = Product::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
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
                'description' => 'nullable|string',
                'palette_category' => 'sometimes|required|' . PaletteTypes::validationRule(),
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
                'image_url' => 'nullable|string',
            ]);

            // Handle image upload if provided
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($product->image_url) {
                    $oldPath = str_replace('/storage/', '', $product->image_url);
                    Storage::disk('public')->delete($oldPath);
                }

                $image = $request->file('image');
                $filename = uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('products', $filename, 'public');
                $validated['image_url'] = '/storage/' . $path;
            }

            $product->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product->fresh(),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
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

            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully',
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
