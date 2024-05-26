<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class breaking extends Model
{
    use HasFactory;
    protected $fillable = [
        'path_id',
        'sorted',
        'name',

    ];

    public function path()
    {
        return $this->belongsTo(Path::class,'path_id');
    }

    public function breaking_Trip()
    {
        return $this->hasMany(breaking_Trip::class);
    }
}
