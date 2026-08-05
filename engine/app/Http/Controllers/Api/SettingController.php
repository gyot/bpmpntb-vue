<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    public function index()
    {
        return response()->json(Settings::first());
    }

    public function update(Request $request)
    {
        Log::info('SettingController@update called', [
            'user' => $request->user()?->email,
            'role' => $request->user()?->role,
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'has_files' => $request->hasFile('logo') || $request->hasFile('favicon'),
            'is_json' => $request->isJson(),
        ]);
        $setting = Settings::firstOrFail();

        $rules = [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'footer' => 'nullable|string|max:1000',
            'facebook' => 'nullable|url|max:500',
            'twitter' => 'nullable|url|max:500',
            'instagram' => 'nullable|url|max:500',
            'youtube' => 'nullable|url|max:500',
            'whatsapp' => 'nullable|string|max:30',
            'alamat' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:30',
            'hp' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'map' => 'nullable|string|max:2000',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'favicon' => 'nullable|image|mimes:jpg,jpeg,png,ico,webp|max:512',
            'primary_color' => 'nullable|string|max:9',
            'secondary_color' => 'nullable|string|max:9',
            'accent_color' => 'nullable|string|max:9',
            'background_color' => 'nullable|string|max:9',
            'surface_color' => 'nullable|string|max:9',
            'text_primary_color' => 'nullable|string|max:9',
            'text_secondary_color' => 'nullable|string|max:9',
            'sidebar_bg_color' => 'nullable|string|max:9',
            'sidebar_text_color' => 'nullable|string|max:9',
            'navbar_bg_color' => 'nullable|string|max:9',
            'navbar_text_color' => 'nullable|string|max:9',
            'ikm_score' => 'nullable|numeric|min:0|max:100',
            'ikm_period' => 'nullable|string|max:100',
            'ikm_link' => 'nullable|url|max:500',
            'silamo_title' => 'nullable|string|max:255',
            'silamo_subtitle' => 'nullable|string|max:255',
            'silamo_schedule' => 'nullable|string|max:255',
            'silamo_meeting_id' => 'nullable|string|max:100',
            'silamo_password' => 'nullable|string|max:100',
            'silamo_link' => 'nullable|url|max:500',
        ];

        $validated = $request->validate($rules);

        $updateData = [];
        foreach ($validated as $key => $value) {
            if ($request->has($key) || $request->hasFile($key)) {
                $updateData[$key] = $value;
            }
        }

        $colorFields = [
            'primary_color', 'secondary_color', 'accent_color',
            'background_color', 'surface_color',
            'text_primary_color', 'text_secondary_color',
            'sidebar_bg_color', 'sidebar_text_color',
            'navbar_bg_color', 'navbar_text_color',
        ];

        foreach ($colorFields as $field) {
            if (isset($updateData[$field])) {
                $updateData[$field] = $this->sanitizeColor($updateData[$field]);
            }
        }

        $uploadDir = public_path('upload/settings');
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        if ($request->hasFile('logo')) {
            $ext = strtolower($request->file('logo')->getClientOriginalExtension());
            $logoName = Str::random(20) . '.' . $ext;
            $request->file('logo')->move($uploadDir, $logoName);
            if ($setting->logo) {
                $old = $uploadDir . '/' . $setting->logo;
                if (file_exists($old)) unlink($old);
            }
            $updateData['logo'] = $logoName;
        }

        if ($request->hasFile('favicon')) {
            $ext = strtolower($request->file('favicon')->getClientOriginalExtension());
            $faviconName = Str::random(20) . '.' . $ext;
            $request->file('favicon')->move($uploadDir, $faviconName);
            if ($setting->favicon) {
                $old = $uploadDir . '/' . $setting->favicon;
                if (file_exists($old)) unlink($old);
            }
            $updateData['favicon'] = $faviconName;
        }

        if (!empty($updateData)) {
            $setting->update($updateData);
        }
        Cache::forget('global_settings');
        Cache::forget('global_theme');

        return response()->json(['message' => 'Setting berhasil diperbarui', 'data' => $setting->fresh()]);
    }

    private function sanitizeColor(?string $color): ?string
    {
        if (empty($color)) return null;
        $color = trim($color);
        if (preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6}|[0-9A-Fa-f]{8})$/', $color)) {
            return $color;
        }
        return null;
    }
}
