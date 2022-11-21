<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'type',
        'first_name',
        'last_name',
        'company',
        'email',
        'phone',
        'country',
        'state',
        'city',
        'postcode',
        'address_1',
        'address_2',
    ];
}
