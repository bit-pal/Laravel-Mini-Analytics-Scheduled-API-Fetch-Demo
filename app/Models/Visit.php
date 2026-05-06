<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visit extends Model
{
    protected $fillable = [
        'site_id',
        'visited_at',
        'ip',
        'city',
        'device',
        'user_agent',
        'url',
        'referrer',
        'timezone',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
