<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tickt extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'comp_trip_id',
        'price',
        'status',


    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function Comp_trip()
    {
        return $this->belongsTo(Comp_trip::class,'comp_trip_id');
    }
}
