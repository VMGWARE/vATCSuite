<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Airport extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $fillable = [
        'icao',
        'name',
        'runways',
    ];

    protected $casts = [
        'runways' => 'array',
    ];
}
