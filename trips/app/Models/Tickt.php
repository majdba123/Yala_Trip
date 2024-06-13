<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tickt extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'bus__trip_id',
        'num_passenger',
        'price',
        'type',
        'status',


    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function Bus_Trip()
    {
        return $this->belongsTo(Bus_Trip::class,'bus__trip_id');
    }
}
