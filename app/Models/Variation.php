<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variation extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'unique_id',
        'variation_value',
        'image_id',
        'sku',
        'regular_price',
        'sale_price',
        'description',
    ];


    static function saveVariation($data){
        $data = Variation::updateOrCreate([
            'id' => $data['id'],
        ],[
            'product_id' => (isset($data['product_id']) ? $data['product_id'] : 0),
            'unique_id' => (isset($data['unique_id']) ? $data['unique_id'] : ''),
            'variation_value' => (isset($data['variation_value']) ? $data['variation_value'] : '') ,
            'image_id' => (isset($data['image_id']) ? $data['image_id'] : 0),
            'sku' => (isset($data['sku']) ? $data['sku'] : ''),
            'regular_price' => (isset($data['regular_price']) ? $data['regular_price'] : ''),
            'sale_price' => (isset($data['sale_price']) ? $data['sale_price'] : ''),
            'description' => (isset($data['description']) ? $data['description'] : ''),
        ]);

        return true;
    }
}
