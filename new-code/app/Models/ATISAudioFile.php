<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ATISAudioFile extends Model
{
    use HasFactory;

    // Table name
    protected $table = 'atis_audio_files';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'icao',
        'ident',
        'atis',
        'zulu',
        'url',
        'file_name',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'icao' => 'string',
        'ident' => 'string',
        'atis' => 'string',

        'url' => 'string',
        'file_name' => 'string',
    ];
}
