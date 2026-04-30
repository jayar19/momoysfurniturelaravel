<?php

namespace App\Http\Controllers\Api;

use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestimonialApiController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        if ($request->boolean('all')) {
            $admin = $this->requireAdmin($request);
            if ($admin instanceof JsonResponse) return $admin;
            $query = Testimonial::query();
        } else {
            $query = Testimonial::where('approved', true);
        }

        return response()->json($query->orderByDesc('created_at')->get()->map(fn (Testimonial $testimonial) => $this->payload($testimonial)));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'userId' => ['nullable', 'string'],
        ]);

        $testimonial = Testimonial::create([
            'name' => $data['name'],
            'message' => $data['message'],
            'rating' => $data['rating'] ?? null,
            'user_id' => $data['userId'] ?? null,
            'approved' => false,
        ]);

        return response()->json($this->payload($testimonial), 201);
    }

    public function show(Request $request, Testimonial $testimonial): JsonResponse
    {
        $admin = $this->requireAdmin($request);
        if ($admin instanceof JsonResponse) return $admin;

        return response()->json($this->payload($testimonial));
    }

    public function update(Request $request, Testimonial $testimonial): JsonResponse
    {
        $admin = $this->requireAdmin($request);
        if ($admin instanceof JsonResponse) return $admin;

        $data = $request->validate([
            'approved' => ['sometimes', 'boolean'],
            'name' => ['sometimes', 'string', 'max:255'],
            'message' => ['sometimes', 'string'],
            'rating' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:5'],
        ]);

        $testimonial->update($data);

        return response()->json($this->payload($testimonial->refresh()));
    }

    public function destroy(Request $request, Testimonial $testimonial): JsonResponse
    {
        $admin = $this->requireAdmin($request);
        if ($admin instanceof JsonResponse) return $admin;

        $testimonial->delete();

        return response()->json(['message' => 'Testimonial deleted successfully']);
    }

    private function payload(Testimonial $testimonial): array
    {
        return [
            'id' => (string) $testimonial->id,
            'name' => $testimonial->name,
            'message' => $testimonial->message,
            'rating' => $testimonial->rating,
            'userId' => $testimonial->user_id,
            'approved' => (bool) $testimonial->approved,
            'createdAt' => $this->iso($testimonial->created_at),
            'updatedAt' => $this->iso($testimonial->updated_at),
        ];
    }
}
