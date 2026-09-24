<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $table = 'projects';

    protected $fillable = [
        'title',
        'video',
        'cover',
        'description',
        'subject',
        'client',
        'project_date',
        'type',
        'project_description',
        'campaign_description',
        'project_cast',
        'behind_the_scenes_video',
        'is_marked',
    ];

    protected $casts = [
        'project_date' => 'date',
        'is_marked' => 'boolean',

    ];

    public function translations(): HasMany
    {
        return $this->hasMany(
            ProjectTranslation::class,
            'project_id'
        );
    }

    public function campaignImages(): HasMany
    {
        return $this->hasMany(
            ProjectCampaignImage::class,
            'project_id'
        )->orderBy('sort_order');
    }

    public function images(): HasMany
    {
        return $this->hasMany(
            ProjectImage::class,
            'project_id'
        )->orderBy('sort_order');
    }
}
