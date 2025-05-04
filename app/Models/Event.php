<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = ['name', 'description', 'event_type_id'];

    // Relationship with EventType (One-to-Many)
    public function eventType()
    {
        return $this->belongsTo(EventType::class);
    }

    // Many-to-Many relationship with Venue
    public function venues()
    {
        return $this->belongsToMany(Venue::class, 'event_venues');
    }
}
