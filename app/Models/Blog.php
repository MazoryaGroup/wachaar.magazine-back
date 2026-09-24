<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Blog extends Model
{
    protected $table = 'blogs';

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'featured_image',
        'author_type',
        'author_id',
        'category_id',
        'published_at',
        'status',
        'content',
        'reading_time',
        'views',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'reading_time' => 'integer',
        'views' => 'integer',
        'author_id' => 'integer',
        'category_id' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            BlogCategory::class,
            'category_id'
        );
    }

    public function images(): HasMany
    {
        return $this->hasMany(
            BlogImage::class,
            'blog_id'
        )->orderBy('sort_order');
    }

    public function artist(): BelongsTo
    {
        return $this->belongsTo(
            Artist::class,
            'author_id'
        );
    }

    public function getAuthorNameAttribute(): string
    {
        if ($this->author_type === 'website') {
            return 'Wachaar';
        }

        if ($this->author_type === 'artist' && $this->artist) {
            return trim(
                $this->artist->first_name . ' ' .
                $this->artist->last_name
            );
        }

        return 'Unknown';
    }
}
