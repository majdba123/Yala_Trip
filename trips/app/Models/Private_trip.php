<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Private_trip extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'from',
        'to',
        'date',
        'status',
        'time',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function Order_private()
    {
        return $this->hasMany(Order_private::class);
    }
}
