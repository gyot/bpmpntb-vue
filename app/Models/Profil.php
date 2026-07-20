<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profil extends Model
{
    protected $table = 'profil';
    protected $fillable = ['id_kategori','slug','title','content','images','thumbnail','status','viewer','tags','writer','tanggal','file'];
    protected $casts = ['tanggal' => 'date'];

    public function Kategori() { return $this->belongsTo(KategoriProfil::class, 'id_kategori'); }
    public function teaser() { return substr(strip_tags($this->content ?? ''), 0, 250); }
    public function getJenisAttribute() { return $this->table; }
    public function status_text() { return HelperData::status_publish()[$this->status] ?? ''; }
}
