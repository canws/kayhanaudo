<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZooneRegion extends Model
{
    use HasFactory;
    protected $fillable = [
        'zoone_id',
        'zoone_region',
    ];
}
