<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiFetch extends Model
{
    protected $fillable = [
        'source',
        'external_id',
        'payload',
        'fetched_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'fetched_at' => 'datetime',
    ];
}
