<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMenuAccess extends Model
{
    protected $table = 'user_menu_access';
    protected $fillable = ['user_id', 'menu_key'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
