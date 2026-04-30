<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthApiController extends ApiController
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'fullName' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::create([
            'name' => ($data['fullName'] ?? '') ?: $data['email'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => User::query()->exists() ? 'customer' : 'admin',
        ]);

        return response()->json($this->sessionPayload($user), 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json([
                'error' => 'Incorrect email or password.',
                'code' => 'auth/invalid-credential',
            ], 401);
        }

        return response()->json($this->sessionPayload($user));
    }

    public function logout(Request $request): JsonResponse
    {
        $header = $request->header('Authorization', '');
        if (str_starts_with($header, 'Bearer ')) {
            DB::table('api_tokens')->where('token_hash', hash('sha256', trim(substr($header, 7))))->delete();
        }

        return response()->json(['success' => true]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        return response()->json($this->userPayload($user));
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'fullName' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update(['name' => $data['fullName'] ?? $data['name'] ?? $user->name]);

        return response()->json($this->userPayload($user));
    }

    private function sessionPayload(User $user): array
    {
        $plainToken = Str::random(64);
        DB::table('api_tokens')->insert([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => now()->addDays(30),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'token' => $plainToken,
            'user' => $this->userPayload($user),
        ];
    }
}
