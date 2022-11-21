<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'coupon_code',
        'description',
        'coupon_amount',
        'discount_type',
        'free_shipping',
        'expiry_date',
        'minimum_spend',
        'maximum_spend',
        'individual_use',
        'exclude_sale_items',
        'includeProductIds',
        'excludeProductIds',
        'usage_limit',
    ];
}
