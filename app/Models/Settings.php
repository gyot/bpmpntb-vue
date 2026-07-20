<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $table = 'settings';
    protected $fillable = [
        'logo', 'title', 'description', 'footer', 'favicon',
        'facebook', 'twitter', 'instagram', 'youtube', 'whatsapp',
        'alamat', 'phone', 'hp', 'email', 'map',
        'primary_color', 'secondary_color', 'accent_color',
        'background_color', 'surface_color',
        'text_primary_color', 'text_secondary_color',
        'sidebar_bg_color', 'sidebar_text_color',
        'navbar_bg_color', 'navbar_text_color',
        'ikm_score', 'ikm_period', 'ikm_link',
    ];

    protected $appends = ['theme_config'];

    public function url_logo()
    {
        if ($this->logo) return asset('upload/settings/' . $this->logo);
        return '';
    }

    public function url_favicon()
    {
        if ($this->favicon) return asset('upload/settings/' . $this->favicon);
        return '';
    }

    public function getThemeConfigAttribute()
    {
        return [
            'primary_color' => $this->primary_color ?? '#2563eb',
            'secondary_color' => $this->secondary_color ?? '#1f2937',
            'accent_color' => $this->accent_color ?? '#f59e0b',
            'background_color' => $this->background_color ?? '#f9fafb',
            'surface_color' => $this->surface_color ?? '#ffffff',
            'text_primary_color' => $this->text_primary_color ?? '#1f293b',
            'text_secondary_color' => $this->text_secondary_color ?? '#6b7280',
            'sidebar_bg_color' => $this->sidebar_bg_color ?? '#1f2937',
            'sidebar_text_color' => $this->sidebar_text_color ?? '#e5e7eb',
            'navbar_bg_color' => $this->navbar_bg_color ?? '#1e40af',
            'navbar_text_color' => $this->navbar_text_color ?? '#ffffff',
        ];
    }

    public static function getDefaultTheme()
    {
        return [
            'primary_color' => '#2563eb',
            'secondary_color' => '#1f2937',
            'accent_color' => '#f59e0b',
            'background_color' => '#f9fafb',
            'surface_color' => '#ffffff',
            'text_primary_color' => '#1f293b',
            'text_secondary_color' => '#6b7280',
            'sidebar_bg_color' => '#1f2937',
            'sidebar_text_color' => '#e5e7eb',
            'navbar_bg_color' => '#1e40af',
            'navbar_text_color' => '#ffffff',
        ];
    }
}
