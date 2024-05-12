<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;
    protected $fillable = [
        'path_id',
        'driver_id',
        'price',
        'status',
        'num_passenger',


    ];

    public function Path()
    {
        return $this->belongsTo(Path::class,'path_id');
    }

    public function Driver()
    {
        return $this->belongsTo(Driver::class , 'driver_id');
    }

    public function Reservation()
    {
        return $this->hasMany(Reservation::class);
    }

    public function Rating()
    {
        return $this->hasMany(Rating::class);
    }
}
