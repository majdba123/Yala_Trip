<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'model_car',
        'number_car',
        'color_car',
        'type_driver',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function Trip()
    {
        return $this->hasMany(Trip::class);
    }

    public function Order_private()
    {
        return $this->hasMany(Order_private::class);
    }
    public function Driver_Bus()
    {
        return $this->hasMany(Driver_Bus::class);
    }
}
