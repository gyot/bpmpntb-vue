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
        'broadcast' => 'Broadcast',
        'ppid' => 'PPID',
        'pengaturan' => 'Pengaturan',
    ];

    private array $allSubMenus = [
        'konten' => [
            'berita' => 'Berita',
            'artikel' => 'Artikel',
            'buletin' => 'Buletin',
            'jurnal' => 'Jurnal',
            'kliping' => 'Kliping',
            'pengumuman' => 'Pengumuman',
            'galeri' => 'Galeri',
            'unduhan' => 'Unduhan',
            'profil' => 'Profil',
            'renstra' => 'Renstra',
            'lakin' => 'Lakin',
            'perjanjian_kinerja' => 'Perjanjian Kinerja',
        ],
        'kategori' => [
            'berita' => 'Berita',
            'artikel' => 'Artikel',
            'buletin' => 'Buletin',
            'jurnal' => 'Jurnal',
            'kliping' => 'Kliping',
            'pengumuman' => 'Pengumuman',
            'galeri' => 'Galeri',
            'unduhan' => 'Unduhan',
            'profil' => 'Profil',
            'renstra' => 'Renstra',
            'lakin' => 'Lakin',
            'perjanjian_kinerja' => 'Perjanjian Kinerja',
        ],
        'media' => [
            'sliders' => 'Sliders',
            'layanan' => 'Layanan',
            'link_eksternal' => 'Link Eksternal',
        ],
        'chatbot' => [
            'chatbot_dashboard' => 'Dashboard',
            'intent' => 'Intent',
            'livechat' => 'Live Chat',
            'analytics' => 'Analytics',
            'knowledge_base' => 'Knowledge Base',
            'konfigurasi_ai' => 'Konfigurasi AI',
            'whatsapp' => 'WhatsApp Gateway',
        ],
        'broadcast' => [
            'wa_broadcast' => 'WhatsApp Broadcast',
        ],
        'ppid' => [
            'kelola_ppid' => 'Kelola PPID',
        ],
        'pengaturan' => [
            'website' => 'Website',
            'tema_website' => 'Tema Website',
            'users' => 'Users',
        ],
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

        if ($target->role === 'superadmin' && session('local_role') !== 'superadmin') {
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
        $currentUserId = session('siamin_user.id_user');

        if ($target->id === $currentUserId) {
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
        return response()->json([
            'menus' => $this->allMenus,
            'subMenus' => $this->allSubMenus,
        ]);
    }

    public function getMenuAccess(int $userId)
    {
        $user = User::findOrFail($userId);
        $rows = $user->menuAccess()->select('menu_key', 'sub_menu_key')->get();

        $menus = [];
        $subMenus = [];

        foreach ($rows as $row) {
            if ($row->sub_menu_key === null) {
                $menus[] = $row->menu_key;
            } else {
                $menus[] = $row->menu_key;
                $subMenus[$row->menu_key][] = $row->sub_menu_key;
            }
        }

        $menus = array_values(array_unique($menus));

        return response()->json([
            'user_id' => $userId,
            'menus' => $menus,
            'subMenus' => $subMenus,
        ]);
    }

    public function updateMenuAccess(Request $request, int $userId)
    {
        $user = User::findOrFail($userId);
        $validated = $request->validate([
            'menus' => 'required|array',
            'menus.*' => 'string|max:100',
            'subMenus' => 'nullable|array',
            'subMenus.*' => 'nullable|array',
            'subMenus.*.*' => 'string|max:100',
        ]);

        DB::table('user_menu_access')->where('user_id', $userId)->delete();
        $rows = [];
        $now = now();

        $subMenus = $validated['subMenus'] ?? [];

        foreach ($validated['menus'] as $menu) {
            if (!empty($subMenus[$menu])) {
                foreach ($subMenus[$menu] as $subKey) {
                    $rows[] = [
                        'user_id' => $userId,
                        'menu_key' => $menu,
                        'sub_menu_key' => $subKey,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            } else {
                $rows[] = [
                    'user_id' => $userId,
                    'menu_key' => $menu,
                    'sub_menu_key' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($rows) DB::table('user_menu_access')->insert($rows);

        return response()->json([
            'message' => 'Akses menu berhasil diupdate',
            'menus' => $validated['menus'],
            'subMenus' => $subMenus,
        ]);
    }

    public function myMenuAccess(Request $request)
    {
        $role = session('local_role');
        $id_user = session('siamin_user.id_user');

        if ($role === 'admin') {
            $allSubs = [];
            foreach ($this->allSubMenus as $menu => $subs) {
                $allSubs[$menu] = array_keys($subs);
            }
            return response()->json([
                'menus' => array_keys($this->allMenus),
                'subMenus' => $allSubs,
            ]);
        }

        $rows = DB::table('user_menu_access')->where('user_id', $id_user)
            ->select('menu_key', 'sub_menu_key')->get();

        $menus = [];
        $subMenus = [];

        foreach ($rows as $row) {
            if ($row->sub_menu_key === null) {
                $menus[] = $row->menu_key;
            } else {
                $menus[] = $row->menu_key;
                $subMenus[$row->menu_key][] = $row->sub_menu_key;
            }
        }

        $menus = array_values(array_unique($menus));

        return response()->json([
            'menus' => $menus,
            'subMenus' => $subMenus,
        ]);
    }
}
