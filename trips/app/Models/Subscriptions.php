<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscriptions extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_id',
        'type',
        'price',

    ];
    public function user_subscription()
    {
        return $this->hasMany(user_subscription::class);
    }
    public function company()
    {
        return $this->belongsTo(Company::class,'company_id');
    }
}
