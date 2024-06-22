<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
class breaking extends Model
{
    use HasFactory;
    protected $fillable = [
        'path_id',
        'sorted',
        'name',

    ];

    protected $keyType = 'string'; // Set the key type to UUID
    public $incrementing = false; // Disable auto-incrementing


    public function path()
    {
        return $this->belongsTo(Path::class,'path_id');
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
