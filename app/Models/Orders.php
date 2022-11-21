<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'order_id',
        'transaction_id',
        'customer_email',
        'amount',
        'currency',
        'payment_mode',
        'payment_date',
        'discount_amount',
        'shipping_cost',
        'shipping_method',
        'status',
        'billing_address',
        'shipping_address',
        'payer_details',
        'ordernote',
    ];

}
