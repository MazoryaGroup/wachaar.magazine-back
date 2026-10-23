<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected array $projectTranslations = [];

    protected array $galleryImages = [];

    /**
     * Prepare project data before creation.
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

        $this->galleryImages = $data['gallery_uploads'] ?? [];

        /*
        |--------------------------------------------------------------------------
        | Remove Non-Project Fields
        |--------------------------------------------------------------------------
        */

        unset(
            $data['translations'],
            $data['gallery_uploads'],
            $data['gallery_images']
        );

        return $data;
    }

    /**
     * Save translations and gallery after project creation.
     */
    protected function afterCreate(): void
    {
        $project = $this->record;

        /*
        |--------------------------------------------------------------------------
        | Save Translations
        |--------------------------------------------------------------------------
        */

        foreach ($this->projectTranslations as $key => $translation) {
            if (!is_array($translation)) {
                continue;
            }

            $locale = $translation['locale'] ?? (
            in_array($key, ['fa', 'en'], true)
                ? $key
                : null
            );

            if (!in_array($locale, ['fa', 'en'], true)) {
                Log::warning('Wachaar: translation skipped', [
                    'project_id' => $project->id,
                    'key' => $key,
                ]);

                continue;
            }

            unset(
                $translation['lang'],
                $translation['locale']
            );

            $project->translations()->updateOrCreate(
                ['locale' => $locale],
                $translation
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Save Gallery Images
        |--------------------------------------------------------------------------
        */

        foreach (array_values($this->galleryImages) as $index => $imagePath) {
            if (!is_string($imagePath) || empty($imagePath)) {
                continue;
            }

            $project->images()->create([
                'image' => $imagePath,
                'sort_order' => $index + 1,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Log
        |--------------------------------------------------------------------------
        */

        Log::info('Wachaar project created successfully', [
            'project_id' => $project->id,
            'translations_count' => count($this->projectTranslations),
            'gallery_count' => count($this->galleryImages),
        ]);
    }
}
