<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $table = 'services';

    protected $fillable = [
        'image_1',
        'image_2',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(ServiceTranslation::class);
    }

    public function translation(string $locale): ?ServiceTranslation
    {
        return $this->translations()
            ->where('locale', $locale)
            ->first();
    }
}
