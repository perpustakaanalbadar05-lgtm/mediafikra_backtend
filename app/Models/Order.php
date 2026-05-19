<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'nama_pembeli', 'whatsapp', 'alamat', 'buku_id',
        'qty', 'total', 'catatan', 'status',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class, 'buku_id');
    }
}
