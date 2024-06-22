<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
class Bus_Trip extends Model
{
    use HasFactory;
    protected $fillable = [
        'status',
        'comp_trip_id',
        'bus_id'
    ];

    protected $keyType = 'string'; // Set the key type to UUID
    public $incrementing = false; // Disable auto-incrementing


    public function bus()
    {
        return $this->belongsTo(Bus::class,'bus_id');
    }

    public function comp_trip()
    {
        return $this->belongsTo(Comp_trip::class,'comp_trip_id');
    }
    public function Tickt()
    {
        return $this->hasMany(Tickt::class);
    }

    public static function boot() {
        parent::boot();
        // Auto generate UUID when creating data User
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }
}
