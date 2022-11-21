<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddOnOptions extends Model
{
    use HasFactory;

    protected $fillable = [
        'add_ons_id',
        'option_title',
        'option_price',
    ];
}
