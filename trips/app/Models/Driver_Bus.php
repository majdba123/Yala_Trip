<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver_Bus extends Model
{
    use HasFactory;
    protected $fillable = [
        'bus_id',
        'driver_Company_id',
        'status',
    ];

    public function Bus()
    {
        return $this->belongsTo(Bus::class,'bus_id');
    }

    public function Driver()
    {
        return $this->belongsTo(Driver_Company::class,'driver_Company_id');
    }

}
