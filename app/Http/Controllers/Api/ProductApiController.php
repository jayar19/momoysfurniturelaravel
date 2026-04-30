<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductApiController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->when($request->query('category'), fn ($query, $category) => $query->where('category', $category))
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Product $product) => $this->payload($product));

        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $admin = $this->requireAdmin($request);
        if ($admin instanceof JsonResponse) {
            return $admin;
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'imageUrl' => ['nullable', 'string'],
            'modelUrl' => ['nullable', 'string'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'variants' => ['nullable', 'array'],
        ]);

        $product = Product::create([
            'name' => $data['name'],
            'category' => $data['category'],
            'price' => $data['price'],
            'description' => $data['description'] ?? '',
            'image_url' => $data['imageUrl'] ?? '',
            'model_url' => $data['modelUrl'] ?? '',
            'stock' => $data['stock'] ?? 0,
            'variants' => $data['variants'] ?? [],
        ]);

        return response()->json($this->payload($product), 201);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json($this->payload($product));
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $admin = $this->requireAdmin($request);
        if ($admin instanceof JsonResponse) {
            return $admin;
        }

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'category' => ['sometimes', 'string', 'max:255'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'description' => ['sometimes', 'nullable', 'string'],
            'imageUrl' => ['sometimes', 'nullable', 'string'],
            'modelUrl' => ['sometimes', 'nullable', 'string'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'variants' => ['sometimes', 'array'],
        ]);

        $update = collect($data)->only(['name', 'category', 'price', 'description', 'stock', 'variants'])->all();
        if (array_key_exists('imageUrl', $data)) {
            $update['image_url'] = $data['imageUrl'];
        }
        if (array_key_exists('modelUrl', $data)) {
            $update['model_url'] = $data['modelUrl'];
        }

        $product->update($update);

        return response()->json($this->payload($product->refresh()));
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        $admin = $this->requireAdmin($request);
        if ($admin instanceof JsonResponse) {
            return $admin;
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }

    private function payload(Product $product): array
    {
        return [
            'id' => (string) $product->id,
            'name' => $product->name,
            'category' => $product->category,
            'price' => (float) $product->price,
            'description' => $product->description ?? '',
            'imageUrl' => $product->image_url ?? '',
            'modelUrl' => $product->model_url ?? '',
            'stock' => (int) $product->stock,
            'variants' => $product->variants ?? [],
            'createdAt' => $this->iso($product->created_at),
            'updatedAt' => $this->iso($product->updated_at),
        ];
    }
}
