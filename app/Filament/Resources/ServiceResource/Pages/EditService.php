<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use App\Models\ServiceTranslation;
use Filament\Resources\Pages\EditRecord;

class EditService extends EditRecord
{
    protected static string $resource = ServiceResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $fa = ServiceTranslation::where('service_id', $this->record->id)
            ->where('locale', 'fa')
            ->first();

        $en = ServiceTranslation::where('service_id', $this->record->id)
            ->where('locale', 'en')
            ->first();

        $data['translation_fa'] = [
            'title' => $fa?->title,
            'description' => $fa?->description,
            'about_package_title' => $fa?->about_package_title,
            'about_package_description' => $fa?->about_package_description,
            'whats_included_title' => $fa?->whats_included_title,
            'whats_included_description' => $fa?->whats_included_description,
        ];

        $data['translation_en'] = [
            'title' => $en?->title,
            'description' => $en?->description,
            'about_package_title' => $en?->about_package_title,
            'about_package_description' => $en?->about_package_description,
            'whats_included_title' => $en?->whats_included_title,
            'whats_included_description' => $en?->whats_included_description,
        ];

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset(
            $data['translation_fa'],
            $data['translation_en']
        );

        return $data;
    }

    protected function afterSave(): void
    {
        $data = $this->form->getState();

        $this->saveTranslation(
            'fa',
            $data['translation_fa'] ?? []
        );

        $this->saveTranslation(
            'en',
            $data['translation_en'] ?? []
        );
    }

    protected function saveTranslation(string $locale, array $data): void
    {
        ServiceTranslation::updateOrCreate(
            [
                'service_id' => $this->record->id,
                'locale' => $locale,
            ],
            [
                'title' => $data['title'] ?? null,
                'description' => $data['description'] ?? null,
                'about_package_title' => $data['about_package_title'] ?? null,
                'about_package_description' => $data['about_package_description'] ?? null,
                'whats_included_title' => $data['whats_included_title'] ?? null,
                'whats_included_description' => $data['whats_included_description'] ?? null,
            ]
        );
    }
}

