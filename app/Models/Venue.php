<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    public function hall()
    {
        return $this->belongsTo(Hall::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // Many-to-Many relationship with Event
    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_venues');
    }
    protected $fillable = [
        'name',
        'capacity',
        'price',
        'hall_id',  // Foreign key for hall
        'image',  // Multiple images as JSON
    ];

    // Cast the 'image' attribute to an array or object
    protected $casts = [
        'image' => 'array', // This will automatically cast the JSON string to an array when accessed
    ];
}
