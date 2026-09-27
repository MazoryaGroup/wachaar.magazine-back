<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtistPortfolio extends Model
{
    protected $table = 'artist_portfolios';

    protected $fillable = [
        'artist_id',
        'type',
        'file',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'file_url',
    ];

    public function getFileUrlAttribute(): ?string
    {
        return $this->file
            ? asset('storage/' . $this->file)
            : null;
    }

    public function artist(): BelongsTo
    {
        return $this->belongsTo(
            Artist::class,
            'artist_id'
        );
    }
}
