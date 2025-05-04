<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hall extends Model
{
    public function venues()
{
    return $this->hasMany(Venue::class);
}
public function services()
    {
        return $this->hasMany(Service::class);
    }
    protected $fillable = [
        'name',
        'location',
        'image', // Make sure 'image' is fillable
    ];
    protected $casts = [
        'image' => 'array', // This will automatically cast the JSON string to an array when accessed
    ];}
