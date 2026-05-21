<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'judul', 'slug', 'deskripsi', 'sinopsis', 'harga', 'stok',
        'cover_image', 'kategori', 'featured', 'status_publish',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'status_publish' => 'boolean',
    ];
}
