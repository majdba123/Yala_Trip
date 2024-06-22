<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
class Order_private extends Model
{
    use HasFactory;

    protected $fillable = [
        'private_trip_id',
        'driver_id',
        'price',
        'status',
    ];

    protected $keyType = 'string'; // Set the key type to UUID
    public $incrementing = false; // Disable auto-incrementing


    public function Private_trip()
    {
        return $this->belongsTo(Private_trip::class,'private_trip_id');
    }

    public function Driver()
    {
        return $this->belongsTo(Driver::class,'driver_id');
    }

    public static function boot() {
        parent::boot();
        // Auto generate UUID when creating data User
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }

}
