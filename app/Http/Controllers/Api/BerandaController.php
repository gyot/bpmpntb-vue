<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Settings, Slider, ExternalLink, Berita, Artikel, Pengumuman, Profil, Visitor, PpidProfile};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

class BerandaController extends Controller
{
    public function index()
    {
        $data = Cache::remember('beranda_index', 300, function () {
            $sliders = Slider::where('status', 'active')->orderBy('order')->take(5)->get()
                ->map(fn($s) => [
                    'id' => $s->id, 'title' => $s->title, 'description' => $s->description,
                    'image_url' => asset('upload/sliders/' . $s->image), 'link' => $s->link,
                ]);

            $artikel = Artikel::where('status', 1)->latest('tanggal')->take(5)->get();
            $berita = Berita::where('status', 1)->latest('tanggal')->take(5)->get();
            $lastPost = $berita->concat($artikel)->sortByDesc('tanggal')->take(6)->values()
                ->map(fn($p) => [
                    'id' => $p->id, 'title' => $p->title, 'slug' => $p->slug,
                    'writer' => $p->writer,
                    'tanggal' => $p->tanggal?->format('Y-m-d'),
                    'teaser' => $p->teaser(), 'jenis' => $p->jenis,
                    'image_url' => $p->images ? asset('upload/' . $p->jenis . '/thm-' . $p->images) : null,
                    'image_fallback' => $p->images ? asset('upload/' . $p->jenis . '/' . $p->images) : null,
                    'kategori' => $p->Kategori?->title ?? 'Umum',
                ]);

            $externalLinks = ExternalLink::where('status', 1)->get()
                ->map(fn($l) => ['id' => $l->id, 'title' => $l->title, 'link' => $l->link, 'images' => $l->images]);

            $profil = Profil::where('status', 1)->get()->map(fn($p) => ['id' => $p->id, 'title' => $p->title, 'slug' => $p->slug]);

            $pengumuman = Pengumuman::where('status', 1)
                ->where('tanggal', '>=', Carbon::now()->subMonth())
                ->latest('tanggal')->take(1)->get()
                ->map(fn($p) => ['id' => $p->id, 'title' => $p->title, 'content' => $p->content]);

            return compact('sliders', 'lastPost', 'externalLinks', 'profil', 'pengumuman');
        });

        return response()->json($data);
    }

    public function settings()
    {
        $setting = Cache::remember('global_settings', 600, fn() => Settings::first());
        $ppidProfile = PpidProfile::first();
        $navigations = $ppidProfile ? $ppidProfile->navigations : null;
        $data = $setting ? $setting->toArray() : [];
        $data['navigations'] = $navigations;
        return response()->json($data);
    }

    public function theme()
    {
        $theme = Cache::remember('global_theme', 600, function () {
            $setting = Settings::first();
            return $setting ? $setting->theme_config : Settings::getDefaultTheme();
        });
        return response()->json($theme);
    }

    public function visitorStats()
    {
        $stats = Cache::remember('global_visitor_stats', 300, function () {
            return [
                'totalVisitors' => DB::table('visitors')->distinct()->count('ip_address'),
                'todayVisitors' => DB::table('visitors')->where('created_at', '>=', Carbon::today())->distinct()->count('ip_address'),
                'thismonthVisitors' => DB::table('visitors')->where('created_at', '>=', Carbon::now()->startOfMonth())->distinct()->count('ip_address'),
                'onlineVisitors' => DB::table('visitors')->where('created_at', '>=', Carbon::now()->subMinutes(5))->distinct()->count('ip_address'),
            ];
        });
        return response()->json($stats);
    }
}
