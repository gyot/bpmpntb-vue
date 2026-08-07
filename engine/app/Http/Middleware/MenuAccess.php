<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class MenuAccess
{
    public function handle(Request $request, Closure $next, string $menu, ?string $subMenu = null): Response
    {
        if (session('local_role') === 'admin') {
            return $next($request);
        }

        $userId = session('siamin_user.id_user');
        $resolvedSubMenu = $subMenu && str_starts_with($subMenu, '{')
            ? $request->route(trim($subMenu, '{}'))
            : $subMenu;

        foreach (explode('|', $menu) as $candidate) {
            [$candidateMenu, $candidateSubMenu] = array_pad(explode(':', $candidate, 2), 2, $resolvedSubMenu);
            $query = DB::table('user_menu_access')
                ->where('user_id', $userId)
                ->where('menu_key', $candidateMenu);

            if ($candidateSubMenu !== null) {
                $query->where(function ($builder) use ($candidateSubMenu) {
                    $builder->whereNull('sub_menu_key')
                        ->orWhere('sub_menu_key', $candidateSubMenu);
                });
            }

            if ($query->exists()) {
                return $next($request);
            }
        }

        return response()->json(['message' => 'Anda tidak memiliki akses ke menu ini'], 403);
    }
}
