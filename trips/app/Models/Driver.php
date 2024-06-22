<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
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

    protected $keyType = 'string'; // Set the key type to UUID
    public $incrementing = false; // Disable auto-incrementing


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

    public static function boot() {
        parent::boot();
        // Auto generate UUID when creating data User
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }

}
