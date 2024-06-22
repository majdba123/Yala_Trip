<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
class Path extends Model
{
    use HasFactory;
    protected $fillable = [
        'from',
        'to',
        'city',
        'price',
    ];

    protected $keyType = 'string'; // Set the key type to UUID
    public $incrementing = false; // Disable auto-incrementing

    public function Trip()
    {
        return $this->hasMany(Trip::class);
    }
    public function Breaking()
    {
        return $this->hasMany(breaking::class);
    }

    public static function boot() {
        parent::boot();
        // Auto generate UUID when creating data User
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }
}
