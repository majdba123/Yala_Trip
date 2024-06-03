<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comp_trip extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_id',
        'from',
        'to',
        'start_time',
        'end_time',
        'price',
        'type',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class,'company_id');
    }
    public function Tickt()
    {
        return $this->hasMany(Tickt::class);
    }
    public function Bus_Trip()
    {
        return $this->hasMany(Bus_Trip::class);
    }
}
