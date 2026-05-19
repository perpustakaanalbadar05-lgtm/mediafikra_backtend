<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = [
        'nama', 'jabatan', 'foto', 'rating', 'isi_review', 'status_publish',
    ];

    protected $casts = [
        'status_publish' => 'boolean',
    ];
}
