<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CategoryProduct;
use Validator;
use Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class CategoryProductController extends Controller
{
    public function __construct() {
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

        $categories = CategoryProduct::all();
        return response()->json(['data' => $categories], 200);
    }

    public function store(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return $user;
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:category_products',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $category = CategoryProduct::create($request->all());
        return response()->json([
            'message' => 'Kategori berhasil dibuat',
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return $user;
        }

        $category = CategoryProduct::find($id);
        
        if (!$category) {
            return response()->json(['error' => 'Kategori tidak ditemukan'], 404);
        }

        return response()->json(['data' => $category], 200);
    }

    public function update(Request $request, $id)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return $user;
        }

        $category = CategoryProduct::find($id);
        
        if (!$category) {
            return response()->json(['error' => 'Kategori tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:category_products,name,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $category->update($request->all());
        return response()->json(['message' => 'Kategori berhasil diperbarui', 'data' => $category], 200);
    }

    public function destroy(Request $request, $id)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return $user;
        }

        $category = CategoryProduct::find($id);
        
        if (!$category) {
            return response()->json(['error' => 'Kategori tidak ditemukan'], 404);
        }

        $category->delete();
        return response()->json(['message' => 'Kategori berhasil dihapus'], 200);
    }
}
