<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    protected $casts = [
        'items' => 'array',
        'total_amount' => 'float',
        'down_payment' => 'float',
        'remaining_balance' => 'float',
        'current_location' => 'array',
        'estimated_delivery' => 'datetime',
        'paymongo_checkout_created_at' => 'datetime',
        'paymongo_paid_at' => 'datetime',
        'paymongo_last_synced_at' => 'datetime',
        'chat_updated_at' => 'datetime',
    ];
}
