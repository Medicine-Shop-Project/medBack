<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddNewMedicine extends Model
{
    protected $fillable = [
        'name', 
        'category', 
        'manufacturer', 
        'stock', 
        'price', 
        'expiry_date'
    ];

}
