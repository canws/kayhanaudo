<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MethodClassCost extends Model
{
    use HasFactory;
    protected $fillable = [
        'method_id',
        'class_id',
        'shipping_cost',
    ];
}
