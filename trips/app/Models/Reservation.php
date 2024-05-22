<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'trip_id',
        'status',
        'price',
        'num_passenger',

    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function Trip()
    {
        return $this->belongsTo(Trip::class,'trip_id');
    }
}
