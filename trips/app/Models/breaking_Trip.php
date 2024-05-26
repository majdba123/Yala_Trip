<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class breaking_Trip extends Model
{
    use HasFactory;
    protected $fillable = [
        'breaking_id',
        'trip_id',
        'status',
    ];
    public function break()
    {
        return $this->belongsTo(breaking::class,'breaking_id');
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class,'trip_id');
    }

}
