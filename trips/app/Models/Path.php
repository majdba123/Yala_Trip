<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Path extends Model
{
    use HasFactory;
    protected $fillable = [
        'from',
        'to',
        'city',
        'price',
    ];
    public function Trip()
    {
        return $this->hasMany(Trip::class);
    }
}
