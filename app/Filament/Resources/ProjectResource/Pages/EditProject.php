<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\ProjectTranslation;
use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $translations = $this->record
            ->translations()
            ->get()
            ->keyBy('locale');

        $data['translations'] = [
            'en' => [
                'title' => $translations->get('en')?->title,
                'subject' => $translations->get('en')?->subject,
                'description' => $translations->get('en')?->description,
                'project_description' => $translations->get('en')?->project_description,
                'project_cast' => $translations->get('en')?->project_cast,
                'campaign_description' => $translations->get('en')?->campaign_description,
            ],
            'fa' => [
                'title' => $translations->get('fa')?->title,
                'subject' => $translations->get('fa')?->subject,
                'description' => $translations->get('fa')?->description,
                'project_description' => $translations->get('fa')?->project_description,
                'project_cast' => $translations->get('fa')?->project_cast,
                'campaign_description' => $translations->get('fa')?->campaign_description,
            ],
        ];

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['translations']);

        return $data;
    }

    protected function afterSave(): void
    {
        $translations = $this->form->getState()['translations'] ?? [];

        foreach (['en', 'fa'] as $locale) {
            $translation = $translations[$locale] ?? [];

            ProjectTranslation::updateOrCreate(
                [
                    'project_id' => $this->record->id,
                    'locale' => $locale,
                ],
                [
                    'title' => $translation['title'] ?? null,
                    'subject' => $translation['subject'] ?? null,
                    'description' => $translation['description'] ?? null,
                    'project_description' => $translation['project_description'] ?? null,
                    'project_cast' => $translation['project_cast'] ?? null,
                    'campaign_description' => $translation['campaign_description'] ?? null,
                ]
            );
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
