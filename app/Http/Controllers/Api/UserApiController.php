<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserApiController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $admin = $this->requireAdmin($request);
        if ($admin instanceof JsonResponse) return $admin;

        return response()->json(User::orderByDesc('created_at')->get()->map(fn (User $user) => $this->userPayload($user)));
    }

    public function show(Request $request, User $user): JsonResponse
    {
        $admin = $this->requireAdmin($request);
        if ($admin instanceof JsonResponse) return $admin;

        return response()->json($this->userPayload($user));
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $admin = $this->requireAdmin($request);
        if ($admin instanceof JsonResponse) return $admin;

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
