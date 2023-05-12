<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Airport extends Model
{
    use HasFactory;

    protected $fillable = [
        'icao',
        'iata',
        'name',
        'runways',
    ];

    protected $casts = [
        'runways' => 'array',
    ];
}
