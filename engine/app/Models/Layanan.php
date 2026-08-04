<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    protected $table = 'layanans';
    protected $fillable = ['title', 'image', 'content', 'pos_file', 'slug', 'tags', 'writer', 'tanggal', 'link_type', 'link_url', 'link_post_jenis', 'link_post_id', 'order', 'status'];
    protected $casts = ['tanggal' => 'date'];

    public function teaser()
    {
        return substr(strip_tags($this->content ?? ''), 0, 200);
    }

    public function getLinkAttribute()
    {
        if ($this->link_type === 'external' && $this->link_url) return $this->link_url;
        return '/layanan/' . $this->id . '/' . $this->slug;
    }
}
