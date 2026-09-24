<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MagazineTranslation extends Model
{
    protected $table = 'magazine_translations';

    protected $fillable = [
        'magazine_id',
        'locale',
        'title',
        'description',
    ];

    public function magazine(): BelongsTo
    {
        return $this->belongsTo(Magazine::class);
    }
}
