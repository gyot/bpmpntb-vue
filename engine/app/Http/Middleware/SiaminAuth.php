<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SiaminService;
use App\Models\UserRole;

class SiaminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = session('siamin_token');

        if (!$token) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Unauthorized. Silakan login kembali.'], 401);
            }
            return redirect('/login');
        }

        $lastCheck = session('siamin_token_checked_at', 0);
        $now = time();

        if ($now - $lastCheck > 300) {
            $siamin = app(SiaminService::class);
            $result = $siamin->getProfile($token);

            if (!$result['success'] && isset($result['status']) && $result['status'] === 401) {
                session()->flush();
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json(['message' => 'Sesi telah berakhir, silakan login kembali.'], 401);
                }
                return redirect('/login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
            }

            if ($result['success']) {
                session(['siamin_user' => $result['data']['user'] ?? session('siamin_user')]);
                session(['siamin_pegawai' => $result['data']['pegawai'] ?? session('siamin_pegawai')]);
            }

            session(['siamin_token_checked_at' => $now]);
        }

        $role = session('local_role');
        $user = session('siamin_user');
        $pegawai = session('siamin_pegawai');

        $request->attributes->set('siamin_user', $user);
        $request->attributes->set('siamin_pegawai', $pegawai);
        $request->attributes->set('local_role', $role);
        $request->attributes->set('siamin_token', $token);

        return $next($request);
    }
}
