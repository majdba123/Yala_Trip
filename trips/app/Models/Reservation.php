<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
class Reservation extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'trip_id',
        'breaking_id',
        'status',
        'price',
        'num_passenger',

    ];

    protected $keyType = 'string'; // Set the key type to UUID
    public $incrementing = false; // Disable auto-incrementing


    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function Trip()
    {
        return $this->belongsTo(Trip::class,'trip_id');
    }

    public function break()
    {
        return $this->belongsTo(breaking::class,'breaking_id');
    }

    public static function boot() {
        parent::boot();
        // Auto generate UUID when creating data User
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }
}
