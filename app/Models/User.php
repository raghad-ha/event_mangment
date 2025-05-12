<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'hall_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at', // إخفاء حقول إضافية
    ];

    /**
     * The attributes that should be appended to JSON.
     *
     * @var array<string>
     */
    protected $appends = [
        'role_name',
        'hall_name'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // العلاقات
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function hall()
    {
        return $this->belongsTo(Hall::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // Accessors
    /**
     * Get the role_name attribute.
     *
     * @return string|null
     */
    public function getRoleNameAttribute()
    {
        return $this->role->name ?? null;
    }

    /**
     * Get the hall_name attribute.
     *
     * @return string|null
     */
    public function getHallNameAttribute()
    {
        return $this->hall->name ?? null;
    }
}
