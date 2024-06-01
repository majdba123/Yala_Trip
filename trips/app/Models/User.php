<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'point',
        'type',
        'lat',
        'lang'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    public function Reservation()
    {
        return $this->hasMany(Reservation::class);
    }
    public function Driver()
    {
        return $this->hasOne(Driver::class);
    }
    public function Driver_Company()
    {
        return $this->hasOne(Driver_Company::class);
    }
    public function Private_trip()
    {
        return $this->hasMany(Private_trip::class);
    }
    public function Rate_comapny()
    {
        return $this->hasMany(Rate_comapny::class);
    }
    public function Charge_balance()
    {
        return $this->hasMany(Charge_balance::class);
    }
    public function Contuct_us()
    {
        return $this->hasMany(Contuct_us::class);
    }
    public function Subscriptions()
    {
        return $this->hasMany(Subscriptions::class);
    }
    public function Company()
    {
        return $this->hasOne(Company::class);
    }
    public function Rating()
    {
        return $this->hasMany(Rating::class);
    }
    public function Tickt()
    {
        return $this->hasMany(Tickt::class);
    }
}
