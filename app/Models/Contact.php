<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'email',
        'photo_path',
    ];

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }
}
