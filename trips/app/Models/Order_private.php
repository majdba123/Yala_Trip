<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order_private extends Model
{
    use HasFactory;

    protected $fillable = [
        'private_trip_id',
        'driver_id',
        'price',
        'status',
    ];

    public function Private_trip()
    {
        return $this->belongsTo(Private_trip::class,'private_trip_id');
    }

    public function Driver()
    {
        return $this->belongsTo(Driver::class,'driver_id');
    }

}
