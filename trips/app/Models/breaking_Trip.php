<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
class breaking_Trip extends Model
{
    use HasFactory;
    protected $fillable = [
        'breaking_id',
        'trip_id',
        'status',
    ];
    protected $keyType = 'string'; // Set the key type to UUID
    public $incrementing = false; // Disable auto-incrementing

    public function break()
    {
        return $this->belongsTo(breaking::class,'breaking_id');
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class,'trip_id');
    }

    public static function boot() {
        parent::boot();
        // Auto generate UUID when creating data User
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }

}
