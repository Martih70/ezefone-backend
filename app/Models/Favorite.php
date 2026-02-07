<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $fillable = [
        'contact_id',
        'order',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
