<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    protected $fillable = [
        'judul', 'slug', 'isi', 'thumbnail', 'status_publish', 'type',
    ];

    protected $casts = [
        'status_publish' => 'boolean',
    ];
}
