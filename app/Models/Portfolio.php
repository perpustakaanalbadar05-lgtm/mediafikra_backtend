<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    protected $fillable = [
        'cover', 'judul', 'penulis', 'kategori', 'tahun', 'deskripsi',
    ];
}
