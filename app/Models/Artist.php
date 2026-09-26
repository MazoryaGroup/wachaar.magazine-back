<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Artist extends Model
{
    protected $table = 'artists';

    protected $fillable = [
        'client_id',

        'first_name',
        'last_name',

        'profile_image',

        'facebook_url',
        'instagram_url',
        'youtube_url',

        'title_1',
        'description_1',

        'title_2',
        'description_2',

        'status',

        'submitted_at',
        'reviewed_at',

        'rejection_reason',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Client صاحب این Artist Profile
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(
            Client::class,
            'client_id'
        );
    }

    /**
     * Portfolio های Artist
     */
    public function portfolios(): HasMany
    {
        return $this->hasMany(
            ArtistPortfolio::class,
            'artist_id'
        )->orderBy('sort_order');
    }
}
