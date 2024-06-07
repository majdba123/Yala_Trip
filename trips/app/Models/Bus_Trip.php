<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bus_Trip extends Model
{
    use HasFactory;
    protected $fillable = [
        'status',
        'comp_trip_id',
        'bus_id'
    ];

    public function bus()
    {
        return $this->belongsTo(Bus::class,'bus_id');
    }

    public function comp_trip()
    {
        return $this->belongsTo(Comp_trip::class,'comp_trip_id');
    }
    public function Tickt()
    {
        return $this->hasMany(Tickt::class);
    }
}
