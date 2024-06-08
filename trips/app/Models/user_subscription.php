<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class user_subscription extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'subscriptions_id',
        'end_date',
        'date_start',
        'status',
    ];
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function subscriptions()
    {
        return $this->belongsTo(Subscriptions::class,'subscriptions_id');
    }

}
