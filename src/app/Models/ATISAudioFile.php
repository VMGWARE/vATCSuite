<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ATISAudioFile extends Model
{
    use CrudTrait;
    use HasFactory;

    // Table name
    protected $table = 'atis_audio_files';

    // Set custom id
    protected $primaryKey = 'id';

    // Disable auto-incrementing
    public $incrementing = false;

    // Set key type
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'icao',
        'ident',
        'atis',
        'zulu',
        'url',
        'file_name',
        'password',
        'expires_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'string',
        'icao' => 'string',
        'ident' => 'string',
        'atis' => 'string',

        'url' => 'string',
        'file_name' => 'string',

        'password' => 'string',
        'expires_at' => 'datetime',
    ];

    /**
     * Logic to run when creating a new model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = uniqid();
        });
    }
}
