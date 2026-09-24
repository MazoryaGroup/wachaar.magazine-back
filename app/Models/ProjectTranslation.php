<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTranslation extends Model
{
    protected $table = 'project_translations';

    protected $fillable = [
        'project_id',
        'locale',
        'title',
        'subject',
        'description',
        'project_description',
        'campaign_description',
        'project_cast',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(
            Project::class,
            'project_id'
        );
    }
}
