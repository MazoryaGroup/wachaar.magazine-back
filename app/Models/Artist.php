<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Artist extends Model
{
    protected $table = 'artists';

    protected $fillable = [
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
    ];

    public function portfolios(): HasMany
    {
        return $this->hasMany(
            ArtistPortfolio::class,
            'artist_id'
        )->orderBy('sort_order');
    }
}
