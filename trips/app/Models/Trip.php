<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
class Trip extends Model
{
    use HasFactory;
    protected $fillable = [
        'path_id',
        'driver_id',
        'status',
        'num_passenger',


    ];

    protected $keyType = 'string'; // Set the key type to UUID
    public $incrementing = false; // Disable auto-incrementing


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

    public function breaking_Trip()
    {
        return $this->hasMany(breaking_Trip::class);
    }

    public static function boot() {
        parent::boot();
        // Auto generate UUID when creating data User
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }
}
