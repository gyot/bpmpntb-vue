<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SiaminAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $role = $request->attributes->get('local_role') ?? session('local_role');

        if ($role !== 'admin') {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Akses ditolak. Hanya admin yang diizinkan.'], 403);
            }
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}
