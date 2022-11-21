<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZooneMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'zoone_id',
        'shipping_method',
        'method_title',
        'shipping_cost',
        'description',
        'free_shipping_requires',
        'status',
    ];

    public static function getMethodDetailsByZooneId($method,$zoone_id){
            $result = ZooneMethod::orderby('id', 'asc')
            ->select('id', 'zoone_id', 'shipping_method', 'method_title', 'shipping_cost', 'free_shipping_requires', 'status')
            ->where('zoone_id', $zoone_id)
            ->where('status', 'active')
            ->where('shipping_method', $method)
            ->first();
        
        return $result;
    }
}
