<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{


    protected $fillable = ['user_id','venue_id','event_type_id','booking_date','status'];

    public function venue()
{
    return $this->belongsTo(Venue::class);
}

public function eventType()
{
    return $this->belongsTo(EventType::class);
}
public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
