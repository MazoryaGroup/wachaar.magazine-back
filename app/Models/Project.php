<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $table = 'projects';

    protected $fillable = [
        'owner_type',
        'owner_id',

        'video',
        'cover',
        'client',
        'project_date',
        'type',
        'behind_the_scenes_video',
        'is_marked',

        'status',
        'submitted_at',
        'reviewed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'project_date' => 'date',
        'is_marked' => 'boolean',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Owner
    |--------------------------------------------------------------------------
    */

    public function owner()
    {
        if ($this->owner_type === 'artist') {
            return $this->belongsTo(
                Artist::class,
                'owner_id'
            );
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Translations
    |--------------------------------------------------------------------------
    */

    public function translations(): HasMany
    {
        return $this->hasMany(
            ProjectTranslation::class,
            'project_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Campaign Images
    |--------------------------------------------------------------------------
    */

    public function campaignImages(): HasMany
    {
        return $this->hasMany(
            ProjectCampaignImage::class,
            'project_id'
        )->orderBy('sort_order');
    }

    /*
    |--------------------------------------------------------------------------
    | Project Images
    |--------------------------------------------------------------------------
    */

    public function images(): HasMany
    {
        return $this->hasMany(
            ProjectImage::class,
            'project_id'
        )->orderBy('sort_order');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isArtistProject(): bool
    {
        return $this->owner_type === 'artist';
    }

    public function isWachaarProject(): bool
    {
        return $this->owner_type === 'wachaar';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
