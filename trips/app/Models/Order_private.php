<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order_private extends Model
{
    use HasFactory;

    protected $fillable = [
        'private_trip',
        'driver_id',
        'price',
        'status',
    ];

    public function Private_trip()
    {
        return $this->belongsTo(Private_trip::class,'private_trip');
    }

    public function Driver()
    {
        return $this->belongsTo(Driver::class,'driver_id');
    }

}
