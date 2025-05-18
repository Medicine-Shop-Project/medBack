<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'medicine_id',
        'quantity',
        'price_per_unit',
        'sub_total',
    ];

    /**
     * Get the order that owns the OrderItem.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the medicine associated with the OrderItem.
     */
    public function medicine(): BelongsTo
    {
        // Assuming your medicine model is AddNewMedicine
        return $this->belongsTo(AddNewMedicine::class, 'medicine_id');
    }
}
