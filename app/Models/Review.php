<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'reviews';

    // Define the fillable columns
    protected $fillable = [
        'user_id',
        'booking_id',
        'rating',
        'comment',
    ];

    // Define the relationship to the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define the relationship to the Booking model
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
