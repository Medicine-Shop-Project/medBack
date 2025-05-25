<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class AddNewMedicine extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'category',
        'manufacturer',
        'stock',
        'price',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }


}
