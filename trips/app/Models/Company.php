<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function Rate_comapny()
    {
        return $this->hasMany(Rate_comapny::class);
    }
    public function Driver_Company()
    {
        return $this->hasMany(Driver_Company::class);
    }


    public function Comp_trip()
    {
        return $this->hasMany(Comp_trip::class);
    }
    public function Subscriptions()
    {
        return $this->hasMany(Subscriptions::class);
    }
    public function Bus()
    {
        return $this->hasMany(Bus::class);
    }
}
