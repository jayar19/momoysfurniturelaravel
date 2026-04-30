<?php

namespace App\Http\Controllers\Api;

use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandApiController extends ApiController
{
    public function index(): JsonResponse
    {
        return response()->json(Brand::orderBy('name')->get()->map(fn (Brand $brand) => $this->payload($brand)));
    }

    public function store(Request $request): JsonResponse
    {
        $admin = $this->requireAdmin($request);
        if ($admin instanceof JsonResponse) return $admin;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'logoUrl' => ['nullable', 'string'],
        ]);

        $brand = Brand::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'logo_url' => $data['logoUrl'] ?? '',
        ]);

        return response()->json($this->payload($brand), 201);
    }

    public function show(Brand $brand): JsonResponse
    {
        return response()->json($this->payload($brand));
    }

    public function update(Request $request, Brand $brand): JsonResponse
    {
        $admin = $this->requireAdmin($request);
        if ($admin instanceof JsonResponse) return $admin;

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'logoUrl' => ['sometimes', 'nullable', 'string'],
        ]);

        $update = collect($data)->only(['name', 'description'])->all();
        if (array_key_exists('logoUrl', $data)) {
            $update['logo_url'] = $data['logoUrl'];
        }

        $brand->update($update);

        return response()->json($this->payload($brand->refresh()));
    }

    public function destroy(Request $request, Brand $brand): JsonResponse
    {
        $admin = $this->requireAdmin($request);
        if ($admin instanceof JsonResponse) return $admin;

        $brand->delete();

        return response()->json(['message' => 'Brand deleted successfully']);
    }

    private function payload(Brand $brand): array
    {
        return [
            'id' => (string) $brand->id,
            'name' => $brand->name,
            'description' => $brand->description ?? '',
            'logoUrl' => $brand->logo_url ?? '',
            'createdAt' => $this->iso($brand->created_at),
            'updatedAt' => $this->iso($brand->updated_at),
        ];
    }
}
