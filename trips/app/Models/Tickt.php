<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
class Tickt extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'bus__trip_id',
        'num_passenger',
        'price',
        'type',
        'status',
    ];
    protected $keyType = 'string'; // Set the key type to UUID
    public $incrementing = false; // Disable auto-incrementing

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function Bus_Trip()
    {
        return $this->belongsTo(Bus_Trip::class,'bus__trip_id');
    }

    public static function boot() {
        parent::boot();
        // Auto generate UUID when creating data User
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }
}
