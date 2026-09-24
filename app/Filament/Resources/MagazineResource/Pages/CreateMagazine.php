<?php

namespace App\Filament\Resources\MagazineResource\Pages;

use App\Filament\Resources\MagazineResource;
use App\Models\MagazineTranslation;
use Filament\Resources\Pages\CreateRecord;

class CreateMagazine extends CreateRecord
{
    protected static string $resource = MagazineResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset(
            $data['translations']
        );

        return $data;
    }

    protected function afterCreate(): void
    {
        $data = $this->form->getState();

        /*
        |--------------------------------------------------------------------------
        | Persian
        |--------------------------------------------------------------------------
        */

        if (!empty($data['translations']['fa'])) {
            MagazineTranslation::create([
                'magazine_id' => $this->record->id,
                'locale' => 'fa',
                'title' => $data['translations']['fa']['title'] ?? null,
                'description' => $data['translations']['fa']['description'] ?? null,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | English
        |--------------------------------------------------------------------------
        */

        if (!empty($data['translations']['en'])) {
            MagazineTranslation::create([
                'magazine_id' => $this->record->id,
                'locale' => 'en',
                'title' => $data['translations']['en']['title'] ?? null,
                'description' => $data['translations']['en']['description'] ?? null,
            ]);
        }
    }
}
