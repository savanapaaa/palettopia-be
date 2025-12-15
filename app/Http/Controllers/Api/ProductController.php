<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Constants\PaletteTypes;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index(Request $req)
    {
        $query = Product::query();

        if ($palette = $req->query('palette')) {
            $query->where('palette_category', $palette);
        }

        return response()->json($query->latest()->get());
    }

    public function show(Product $product)
    {
        return response()->json($product);
    }

    public function store(Request $request)
{
    $data = $request->validate([
        'name'             => 'required|string|max:255',
        'image_url'        => 'nullable|string|max:500',
        'palette_category' => 'nullable|string|max:100|' . PaletteTypes::validationRule(),
        'description'      => 'nullable|string',
    ]);

    $data['user_id'] = $request->user()->id;

    $product = Product::create($data);

    return response()->json([
        'message' => 'Product created',
        'product' => $product,
    ], 201);
}

public function update(Request $request, Product $product)
{
    $data = $request->validate([
        'name'             => 'sometimes|required|string|max:255',
        'image_url'        => 'sometimes|nullable|string|max:500',
        'palette_category' => 'sometimes|nullable|string|max:100|' . PaletteTypes::validationRule(),
        'description'      => 'sometimes|nullable|string',
    ]);

    $product->update($data);

    return response()->json([
        'message' => 'Product updated',
        'product' => $product,
    ]);
}

public function destroy(Product $product)
{
    $product->delete();

    return response()->json([
        'message' => 'Product deleted',
    ]);
}

public function recommend(Request $request)
{
    $palette = $request->query('palette'); // ?palette=Autumn

    if (!$palette) {
        return response()->json(['message' => 'palette parameter is required'], 422);
    }
    $products = Product::where('palette_tag', $palette)->get();
    return response()->json([
        'palette'  => $palette,
        'products' => $products,
    ]);
}


    // update & destroy mirip, dengan cek admin

    protected function authorizeAdmin(Request $req)
    {
        if ($req->user()->role !== 'admin') {
            abort(403, 'Only admin');
        }
    }
}

