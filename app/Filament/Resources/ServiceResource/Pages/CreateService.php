<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use App\Models\ServiceTranslation;
use Filament\Resources\Pages\CreateRecord;

class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset(
            $data['translation_fa'],
            $data['translation_en']
        );

        return $data;
    }

    protected function afterCreate(): void
    {
        $data = $this->form->getState();

        if (!empty($data['translation_fa'])) {
            ServiceTranslation::create([
                'service_id' => $this->record->id,
                'locale' => 'fa',
                'title' => $data['translation_fa']['title'] ?? null,
                'description' => $data['translation_fa']['description'] ?? null,
                'about_package_title' => $data['translation_fa']['about_package_title'] ?? null,
                'about_package_description' => $data['translation_fa']['about_package_description'] ?? null,
                'whats_included_title' => $data['translation_fa']['whats_included_title'] ?? null,
                'whats_included_description' => $data['translation_fa']['whats_included_description'] ?? null,
            ]);
        }

        if (!empty($data['translation_en'])) {
            ServiceTranslation::create([
                'service_id' => $this->record->id,
                'locale' => 'en',
                'title' => $data['translation_en']['title'] ?? null,
                'description' => $data['translation_en']['description'] ?? null,
                'about_package_title' => $data['translation_en']['about_package_title'] ?? null,
                'about_package_description' => $data['translation_en']['about_package_description'] ?? null,
                'whats_included_title' => $data['translation_en']['whats_included_title'] ?? null,
                'whats_included_description' => $data['translation_en']['whats_included_description'] ?? null,
            ]);
        }
    }
}
