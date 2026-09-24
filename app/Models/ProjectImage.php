<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectImage extends Model
{
    protected $table = 'project_images';

    protected $fillable = [
        'project_id',
        'image',
        'sort_order',
    ];

    protected $casts = [
        'project_id' => 'integer',
        'sort_order' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(
            Project::class,
            'project_id'
        );
    }
}
