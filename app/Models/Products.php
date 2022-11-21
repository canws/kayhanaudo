<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;


class Products extends Model
{
    use HasFactory, Sluggable;
    protected $fillable = [
        'user_id',
        'sku',
        'product_title',
        'slug',
        'product_content',
        'categories',
        'featured_image',
        'gallery_images',
        'product_type',
        'short_discription',
        'regular_price',
        'wholesaler_price',
        'stock_status',
        'shipping_class',
        'dimensions_length',
        'dimensions_width',
        'dimensions_height',
        'weight',
        'video_link',
        'product_specification',
        'make',
        'model',
        'model_year',
        'status',
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'product_title'
            ]
        ];
    }
}
