<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    private function getAuthenticatedUser(Request $request)
    {
        try {
            if (!$token = $request->bearerToken()) {
                return response()->json(['error' => 'Token tidak ditemukan'], 401);
            }
            $user = JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token tidak valid'], 401);
        }
        return $user;
    }

    public function index(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return $user;
        }

        $products = Product::all();
        return response()->json([
            'status' => 'sukses',
            'data' => $products
        ]);
    }

    public function store(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return $user;
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'category_product_id' => 'required|exists:category_products,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $product = new Product();
        $product->name = $request->name;
        $product->price = $request->price;
        $product->category_product_id = $request->category_product_id;
        
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
            $product->image = $imageName;
        }

        $product->save();

        return response()->json([
            'status' => 'sukses',
            'message' => 'Produk berhasil ditambahkan',
            'data' => $product
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return $user;
        }

        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => 'gagal',
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'sukses',
            'data' => $product
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return $user;
        }

        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => 'gagal',
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'price' => 'numeric',
            'category_product_id' => 'exists:category_products,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $product->name = $request->name ?? $product->name;
        $product->price = $request->price ?? $product->price;
        $product->category_product_id = $request->category_product_id ?? $product->category_product_id;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
            $product->image = $imageName;
        }

        $product->save();

        return response()->json([
            'status' => 'sukses',
            'message' => 'Produk berhasil diperbarui',
            'data' => $product
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return $user;
        }

        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => 'gagal',
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        $product->delete();

        return response()->json([
            'status' => 'sukses',
            'message' => 'Produk berhasil dihapus'
        ]);
    }
}
