<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAddOns extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'add_ons_id',
    ];
}
