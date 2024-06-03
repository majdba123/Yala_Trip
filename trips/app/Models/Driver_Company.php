<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver_Company extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'company_id',
        'status',

    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function company()
    {
        return $this->belongsTo(User::class,'company_id');
    }
    public function Bus()
    {
        return $this->hasOne(Bus::class);
    }
}
