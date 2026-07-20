<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, DB};
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    private array $allMenus = [
        'dashboard' => 'Dashboard',
        'konten' => 'Konten',
        'kategori' => 'Kategori',
        'media' => 'Media',
        'chatbot' => 'Si Intan',
        'ppid' => 'PPID',
        'pengaturan' => 'Pengaturan',
    ];

    public function index()
    {
        $users = User::select('id', 'name', 'email', 'role', 'id_seksi', 'created_at')
            ->withCount('menuAccess')
            ->latest()->paginate(25);
        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'id_seksi' => 'nullable|integer',
            'role' => 'required|in:user,admin',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);

        return response()->json(['message' => 'User berhasil ditambahkan', 'user' => $user], 201);
    }

    public function update(Request $request, int $id)
    {
        $target = User::findOrFail($id);
        $currentUser = $request->user();

        if ($target->role === 'superadmin' && $currentUser->role !== 'superadmin') {
            return response()->json(['message' => 'Tidak bisa mengedit superadmin'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'id_seksi' => 'nullable|integer',
            'role' => 'required|in:user,admin',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $target->update($validated);

        return response()->json(['message' => 'User berhasil diupdate', 'user' => $target]);
    }

    public function destroy(Request $request, int $id)
    {
        $target = User::findOrFail($id);
        $currentUser = $request->user();

        if ($target->id === $currentUser->id) {
            return response()->json(['message' => 'Tidak bisa menghapus akun sendiri'], 422);
        }

        if ($target->role === 'superadmin') {
            return response()->json(['message' => 'Tidak bisa menghapus superadmin'], 403);
        }

        $target->delete();

        return response()->json(['message' => 'User berhasil dihapus']);
    }

    public function menus()
    {
        return response()->json($this->allMenus);
    }

    public function getMenuAccess(int $userId)
    {
        $user = User::findOrFail($userId);
        $access = $user->menuAccess()->pluck('menu_key')->toArray();
        return response()->json(['user_id' => $userId, 'menus' => $access]);
    }

    public function updateMenuAccess(Request $request, int $userId)
    {
        $user = User::findOrFail($userId);
        $validated = $request->validate([
            'menus' => 'required|array',
            'menus.*' => 'string|max:100',
        ]);

        DB::table('user_menu_access')->where('user_id', $userId)->delete();
        $rows = [];
        $now = now();
        foreach ($validated['menus'] as $menu) {
            $rows[] = ['user_id' => $userId, 'menu_key' => $menu, 'created_at' => $now, 'updated_at' => $now];
        }
        if ($rows) DB::table('user_menu_access')->insert($rows);

        return response()->json(['message' => 'Akses menu berhasil diupdate', 'menus' => $validated['menus']]);
    }

    public function myMenuAccess(Request $request)
    {
        $user = $request->user();
        if ($user->role === 'admin') {
            return response()->json(array_keys($this->allMenus));
        }
        $access = DB::table('user_menu_access')->where('user_id', $user->id)->pluck('menu_key')->toArray();
        return response()->json($access);
    }
}
