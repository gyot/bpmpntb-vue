<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class VisitorLogger
{
    protected $skipPaths = ['upload/', 'build/', 'assets/', 'favicon.ico', 'robots.txt'];

    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();

        foreach ($this->skipPaths as $skip) {
            if (str_starts_with($path, $skip)) {
                return $next($request);
            }
        }

        if (!$request->isMethod('GET') || $request->ajax()) {
            return $next($request);
        }

        $ip = $request->ip();
        $url = $request->fullUrl();
        $cacheKey = 'visitor-' . md5($ip . $url);

        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey, true, now()->addMinutes(10));

            try {
                DB::table('visitors')->insert([
                    'ip_address' => $ip,
                    'url' => mb_substr($url, 0, 2000),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $e) {
                \Log::warning('VisitorLogger insert failed', ['error' => $e->getMessage()]);
            }
        }

        return $next($request);
    }
}
