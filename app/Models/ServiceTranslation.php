<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceTranslation extends Model
{
    protected $table = 'service_translations';

    protected $fillable = [
        'service_id',
        'locale',
        'title',
        'description',
        'about_package_title',
        'about_package_description',
        'whats_included_title',
        'whats_included_description',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
