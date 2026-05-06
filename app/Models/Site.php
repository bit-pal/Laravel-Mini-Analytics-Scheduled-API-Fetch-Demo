<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    protected $fillable = [
        'name',
        'site_key',
        'allowed_origins',
    ];

    protected $casts = [
        'allowed_origins' => 'array',
    ];

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }
}
