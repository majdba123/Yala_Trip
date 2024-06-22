<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
class Subscriptions extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_id',
        'type',
        'price',

    ];

    protected $keyType = 'string'; // Set the key type to UUID
    public $incrementing = false; // Disable auto-incrementing

    public function user_subscription()
    {
        return $this->hasMany(user_subscription::class);
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
