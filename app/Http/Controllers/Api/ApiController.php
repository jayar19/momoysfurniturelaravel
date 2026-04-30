<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

abstract class ApiController extends Controller
{
    protected function currentUser(Request $request): ?User
    {
        $header = $request->header('Authorization', '');
        if (! str_starts_with($header, 'Bearer ')) {
            return null;
        }

        $token = trim(substr($header, 7));
        if ($token === '') {
            return null;
        }

        $record = DB::table('api_tokens')
            ->where('token_hash', hash('sha256', $token))
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        return $record ? User::find($record->user_id) : null;
    }

    protected function requireUser(Request $request): User|JsonResponse
    {
        return $this->currentUser($request) ?: response()->json(['error' => 'Unauthenticated'], 401);
    }

    protected function requireAdmin(Request $request): User|JsonResponse
    {
        $user = $this->currentUser($request);
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Access denied'], 403);
        }

        return $user;
    }

    protected function iso($value): ?string
    {
        if (! $value) {
            return null;
        }

        return method_exists($value, 'toIso8601String') ? $value->toIso8601String() : (string) $value;
    }

    protected function userPayload(User $user): array
    {
        return [
            'id' => (string) $user->id,
            'uid' => (string) $user->id,
            'name' => $user->name,
            'fullName' => $user->name,
            'email' => $user->email,
            'role' => $user->role ?? 'customer',
            'createdAt' => $this->iso($user->created_at),
            'updatedAt' => $this->iso($user->updated_at),
        ];
    }
}
