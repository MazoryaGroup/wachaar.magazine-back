<?php

namespace App\Filament\Resources\MagazineResource\Pages;

use App\Filament\Resources\MagazineResource;
use App\Models\MagazineTranslation;
use Filament\Resources\Pages\EditRecord;

class EditMagazine extends EditRecord
{
    protected static string $resource = MagazineResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $fa = MagazineTranslation::where('magazine_id', $this->record->id)
            ->where('locale', 'fa')
            ->first();

        $en = MagazineTranslation::where('magazine_id', $this->record->id)
            ->where('locale', 'en')
            ->first();

        $data['translations'] = [
            'fa' => [
                'title' => $fa?->title,
                'description' => $fa?->description,
            ],

            'en' => [
                'title' => $en?->title,
                'description' => $en?->description,
            ],
        ];

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset(
            $data['translations']
        );

        return $data;
    }

    protected function afterSave(): void
    {
        $data = $this->form->getState();

        /*
        |--------------------------------------------------------------------------
        | Persian
        |--------------------------------------------------------------------------
        */

        $this->saveTranslation(
            'fa',
            $data['translations']['fa'] ?? []
        );

        /*
        |--------------------------------------------------------------------------
        | English
        |--------------------------------------------------------------------------
        */

        $this->saveTranslation(
            'en',
            $data['translations']['en'] ?? []
        );
    }

    protected function saveTranslation(string $locale, array $data): void
    {
        MagazineTranslation::updateOrCreate(
            [
                'magazine_id' => $this->record->id,
                'locale' => $locale,
            ],
            [
                'title' => $data['title'] ?? null,
                'description' => $data['description'] ?? null,
            ]
        );
    }
}
