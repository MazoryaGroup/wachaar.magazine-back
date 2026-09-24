<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MagazineHashtag extends Model
{
    protected $table = 'magazine_hashtags';

    protected $fillable = [
        'name',
        'slug',
    ];

    public function magazines(): BelongsToMany
    {
        return $this->belongsToMany(
            Magazine::class,
            'magazine_magazine_hashtag',
            'magazine_hashtag_id',
            'magazine_id'
        );
    }
}
