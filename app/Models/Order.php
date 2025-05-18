<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'total_amount',
        // 'user_id', // if you add it
    ];


    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // If you add user_id:
    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }
}
