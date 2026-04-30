<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HealthApiController
{
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'sessionId' => (string) Str::uuid(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
