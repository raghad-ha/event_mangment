<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventType extends Model
{
    protected $fillable = ['name'];

    // One-to-Many relationship with Event
    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
