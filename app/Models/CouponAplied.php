<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponAplied extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'coupon_id'
    ];

}
