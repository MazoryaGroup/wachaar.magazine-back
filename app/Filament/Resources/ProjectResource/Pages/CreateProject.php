<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\ProjectTranslation;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    /**
     * اطلاعاتی که قبل از ذخیره پروژه از فرم جدا می‌کنیم.
     */
    protected array $projectTranslations = [];

    protected array $galleryImages = [];

    /**
     * آماده‌سازی داده‌های فرم قبل از ایجاد Project
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        /*
        |--------------------------------------------------------------------------
        | Store Translations
        |--------------------------------------------------------------------------
        */

        $this->projectTranslations = $data['translations'] ?? [];

        /*
        |--------------------------------------------------------------------------
        | Store Gallery Images
        |--------------------------------------------------------------------------
        */

        $this->galleryImages = $data['gallery_images'] ?? [];

        /*
        |--------------------------------------------------------------------------
        | Remove Non-Project Fields
        |--------------------------------------------------------------------------
        */

        unset($data['translations']);
        unset($data['gallery_images']);

        return $data;
    }

    /**
     * بعد از ایجاد Project
     */
    protected function afterCreate(): void
    {
        dd($this->galleryImages);
    }
}
