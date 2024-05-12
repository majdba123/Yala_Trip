<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bus extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'num_passenger',
        'status',
        'company_id'

    ];

    public function Driver_Bus()
    {
        return $this->hasMany(Driver_Bus::class);
    }
    public function Bus_Trip()
    {
        return $this->hasMany(Bus_Trip::class);
    }
    public function company()
    {
        return $this->belongsTo(Company::class,'company_id');
    }
}
