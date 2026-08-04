<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            Log::warning('AdminOnly: No authenticated user', [
                'url' => $request->url(),
                'method' => $request->method(),
                'has_token' => $request->bearerToken() ? 'yes' : 'no',
            ]);
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Unauthorized. Tidak ada user terotentikasi.'], 403);
            }
            abort(403, 'Unauthorized');
        }

        if ($user->role !== 'admin' && $user->role !== 'superadmin') {
            Log::warning('AdminOnly: User role not admin', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'url' => $request->url(),
            ]);
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Unauthorized. Akses ditolak. Role: ' . $user->role], 403);
            }
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
