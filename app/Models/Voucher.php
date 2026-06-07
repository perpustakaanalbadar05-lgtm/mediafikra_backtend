<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'min_purchase', 'max_discount',
        'usage_limit', 'used_count', 'valid_until', 'is_active'
    ];
}
