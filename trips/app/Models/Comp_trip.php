<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
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

    protected $keyType = 'string'; // Set the key type to UUID
    public $incrementing = false; // Disable auto-incrementing


    public function company()
    {
        return $this->belongsTo(Company::class,'company_id');
    }

    public function Bus_Trip()
    {
        return $this->hasMany(Bus_Trip::class);
    }

    public static function boot() {
        parent::boot();
        // Auto generate UUID when creating data User
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }
}
