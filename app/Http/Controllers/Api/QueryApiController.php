<?php

namespace App\Http\Controllers\Api;

use App\Models\CustomerQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QueryApiController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $admin = $this->requireAdmin($request);
        if ($admin instanceof JsonResponse) return $admin;

        $queries = CustomerQuery::query()
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (CustomerQuery $query) => $this->payload($query));

        return response()->json($queries);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        $query = CustomerQuery::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'subject' => $data['subject'] ?? '',
            'message' => $data['message'],
            'status' => 'unread',
        ]);

        return response()->json($this->payload($query), 201);
    }

    public function show(Request $request, CustomerQuery $query): JsonResponse
    {
        $admin = $this->requireAdmin($request);
        if ($admin instanceof JsonResponse) return $admin;

        return response()->json($this->payload($query));
    }

    public function update(Request $request, CustomerQuery $query): JsonResponse
    {
        $admin = $this->requireAdmin($request);
        if ($admin instanceof JsonResponse) return $admin;

        $data = $request->validate(['status' => ['required', 'string', 'max:255']]);
        $query->update(['status' => $data['status']]);

        return response()->json($this->payload($query->refresh()));
    }

    public function destroy(Request $request, CustomerQuery $query): JsonResponse
    {
        $admin = $this->requireAdmin($request);
        if ($admin instanceof JsonResponse) return $admin;

        $query->delete();

        return response()->json(['message' => 'Query deleted successfully']);
    }

    private function payload(CustomerQuery $query): array
    {
        return [
            'id' => (string) $query->id,
            'name' => $query->name,
            'email' => $query->email,
            'subject' => $query->subject ?? '',
            'message' => $query->message,
            'status' => $query->status,
            'createdAt' => $this->iso($query->created_at),
            'updatedAt' => $this->iso($query->updated_at),
        ];
    }
}
