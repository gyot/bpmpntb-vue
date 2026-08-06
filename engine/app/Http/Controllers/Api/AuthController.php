<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SiaminService;
use App\Models\UserRole;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string',
            'password' => 'required',
        ]);

        $siamin = app(SiaminService::class);
        $result = $siamin->login($credentials['email'], $credentials['password']);

        if (!$result['success']) {
            return response()->json(['message' => $result['message'] ?? 'Login gagal'], $result['status'] ?? 401);
        }

        $siaminData = $result['data'];
        $siaminUser = $siaminData['user'] ?? null;
        $siaminToken = $siaminData['token'] ?? null;

        if (!$siaminUser || !$siaminToken) {
            return response()->json(['message' => 'Response SIAMIN tidak valid'], 500);
        }

        $id_user = $siaminUser['id_user'];
        $localRole = UserRole::where('id_user', $id_user)->first();

        if (!$localRole) {
            $siamin->logout($siaminToken);
            return response()->json([
                'message' => 'User belum terdaftar di sistem ini. Hubungi admin untuk mendapatkan akses.',
            ], 403);
        }

        session([
            'siamin_token' => $siaminToken,
            'siamin_user' => $siaminUser,
            'siamin_pegawai' => $siaminData['pegawai'] ?? null,
            'siamin_unit_kerja' => $siaminData['unit_kerja'] ?? [],
            'local_role' => $localRole->role,
            'siamin_token_checked_at' => time(),
        ]);

        Log::info('Login berhasil', [
            'id_user' => $id_user,
            'email' => $siaminUser['email'] ?? '-',
            'local_role' => $localRole->role,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'user' => [
                'id_user' => $id_user,
                'email' => $siaminUser['email'] ?? '',
                'role' => $localRole->role,
                'nama' => $siaminData['pegawai']['nama'] ?? $siaminUser['email'] ?? '',
                'nip' => $siaminData['pegawai']['nip'] ?? '',
                'jabatan' => $siaminData['pegawai']['nama_jabatan'] ?? '',
            ],
        ]);
    }

    public function user(Request $request)
    {
        return response()->json([
            'id_user' => session('siamin_user.id_user'),
            'email' => session('siamin_user.email'),
            'role' => session('local_role'),
            'nama' => session('siamin_pegawai.nama', session('siamin_user.email')),
            'nip' => session('siamin_pegawai.nip', ''),
            'jabatan' => session('siamin_pegawai.nama_jabatan', ''),
            'pegawai' => session('siamin_pegawai'),
            'unit_kerja' => session('siamin_unit_kerja', []),
        ]);
    }

    public function logout(Request $request)
    {
        $token = session('siamin_token');
        if ($token) {
            $siamin = app(SiaminService::class);
            $siamin->logout($token);
        }

        session()->flush();

        return response()->json(['success' => true, 'message' => 'Logout berhasil']);
    }

    public function siaminUsers(Request $request)
    {
        $token = session('siamin_token');
        $siamin = app(SiaminService::class);
        $result = $siamin->getAllUsers($token);

        if (!$result['success']) {
            return response()->json(['message' => $result['message'] ?? 'Gagal mengambil data'], 500);
        }

        $siaminUsers = $result['data'];
        $localRoles = UserRole::whereIn('id_user', collect($siaminUsers)->pluck('id_user'))->get()->keyBy('id_user');

        $merged = collect($siaminUsers)->map(function ($u) use ($localRoles) {
            $local = $localRoles->get($u['id_user']);
            $u['local_role'] = $local->role ?? null;
            $u['local_role_id'] = $local->id ?? null;
            return $u;
        });

        return response()->json($merged);
    }

    public function setRole(Request $request)
    {
        $validated = $request->validate([
            'id_user' => 'required|integer',
            'role' => 'required|string|max:50',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
        ]);

        $userRole = UserRole::updateOrCreate(
            ['id_user' => $validated['id_user']],
            [
                'role' => $validated['role'],
                'name' => $validated['name'] ?? null,
                'email' => $validated['email'] ?? null,
            ]
        );

        return response()->json(['success' => true, 'message' => 'Role berhasil disimpan', 'data' => $userRole]);
    }

    public function revokeRole(Request $request, int $id_user)
    {
        $deleted = UserRole::where('id_user', $id_user)->delete();

        if ($deleted) {
            return response()->json(['success' => true, 'message' => 'Akses user berhasil dicabut']);
        }

        return response()->json(['message' => 'User tidak ditemukan'], 404);
    }

    public function localRoles()
    {
        return response()->json(UserRole::orderBy('id', 'desc')->get());
    }
}
