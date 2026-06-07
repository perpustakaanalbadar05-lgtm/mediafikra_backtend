<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'judul', 'slug', 'deskripsi', 'sinopsis', 'harga', 'stok',
        'cover_image', 'kategori', 'featured', 'status_publish',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'status_publish' => 'boolean',
    ];
}
