<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;
    // The name of the table (if different from the pluralized model name)
    protected $table = 'services';

    // Define the fillable columns
    protected $fillable = [
        'name',  // Name of the service
        'price', // Price of the service
    ];
}
