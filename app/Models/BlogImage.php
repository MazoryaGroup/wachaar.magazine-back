<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogImage extends Model
{
    protected $table = 'blog_images';

    protected $fillable = [
        'blog_id',
        'image',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function blog(): BelongsTo
    {
        return $this->belongsTo(
            Blog::class,
            'blog_id'
        );
    }
}
