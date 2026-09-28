<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Magazine extends Model
{
    protected $table = 'magazines';

    protected $fillable = [
        'cover_image',
        'pdf',
        'published_at',
        'pages_count',
    ];

    protected $casts = [
        'published_at' => 'date',
        'pages_count' => 'integer',
    ];

    public function hashtags(): BelongsToMany
    {
        return $this->belongsToMany(
            MagazineHashtag::class,
            'magazine_magazine_hashtag',
            'magazine_id',
            'magazine_hashtag_id'
        );
    }

    public function translations(): HasMany
    {
        return $this->hasMany(MagazineTranslation::class);
    }

    public function translation(string $locale): ?MagazineTranslation
    {
        if (!in_array($locale, ['fa', 'en'], true)) {
            return null;
        }

        return $this->translations()
            ->where('locale', $locale)
            ->first();
    }
}
