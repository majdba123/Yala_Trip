<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
class Bus extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'num_passenger',
        'status',
        'company_id',
        'driver__company_id'

    ];

    protected $keyType = 'string'; // Set the key type to UUID
    public $incrementing = false; // Disable auto-incrementing

    public function Driver_company()
    {
        return $this->belongsTo(Driver_Company::class , 'driver__company_id');
    }
    public function Bus_Trip()
    {
        return $this->hasMany(Bus_Trip::class);
    }
    public function company()
    {
        return $this->belongsTo(Company::class,'company_id');
    }
    public static function boot() {
        parent::boot();
        // Auto generate UUID when creating data User
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }
}
