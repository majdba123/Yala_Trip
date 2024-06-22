<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
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
    protected $keyType = 'string'; // Set the key type to UUID
    public $incrementing = false; // Disable auto-incrementing


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
    public function user_subscription()
    {
        return $this->hasMany(user_subscription::class);
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

    public static function boot() {
        parent::boot();
        // Auto generate UUID when creating data User
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }
}
