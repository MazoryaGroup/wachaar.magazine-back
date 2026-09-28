<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Mail\ProjectApprovedMail;
use App\Mail\ProjectdraftMail;
use App\Mail\ProjectRejectedMail;
use App\Models\Artist;
use App\Models\Client;
use App\Models\ProjectTranslation;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected ?string $previousStatus = null;

    /*
    |--------------------------------------------------------------------------
    | Get Previous Status
    |--------------------------------------------------------------------------
    */

    protected function beforeSave(): void
    {
        $this->previousStatus = $this->record->status;
    }

    /*
    |--------------------------------------------------------------------------
    | Fill Form
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Before Save
    |--------------------------------------------------------------------------
    */

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['translations']);

        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | After Save
    |--------------------------------------------------------------------------
    */

    protected function afterSave(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Save Translations
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Status Email
        |--------------------------------------------------------------------------
        */

        $project = $this->record;

        $newStatus = $project->status;

        /*
        | اگر وضعیت تغییر نکرده، ایمیلی ارسال نشود.
        */

        if ($this->previousStatus === $newStatus) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Get Project Owner Email
        |--------------------------------------------------------------------------
        */

        $email = $this->getProjectOwnerEmail($project);

        if (!$email) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Send Email
        |--------------------------------------------------------------------------
        */
        if ($newStatus === 'draft') {
            Mail::to($email)->send(
                new ProjectdraftMail($project)
            );

            return;
        }

        if ($newStatus === 'approved') {
            Mail::to($email)->send(
                new ProjectApprovedMail($project)
            );

            return;
        }

        if ($newStatus === 'rejected') {
            Mail::to($email)->send(
                new ProjectRejectedMail($project)
            );

            return;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get Project Owner Email
    |--------------------------------------------------------------------------
    */

    private function getProjectOwnerEmail($project): ?string
    {
        if (
            empty($project->owner_type) ||
            empty($project->owner_id)
        ) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Artist Owner
        |--------------------------------------------------------------------------
        */

        if (
            $project->owner_type === Artist::class ||
            $project->owner_type === 'artist' ||
            str_ends_with($project->owner_type, '\\Artist')
        ) {
            $artist = Artist::with('client')
                ->find($project->owner_id);

            return $artist?->client?->email;
        }

        /*
        |--------------------------------------------------------------------------
        | Client Owner
        |--------------------------------------------------------------------------
        */

        if (
            $project->owner_type === Client::class ||
            $project->owner_type === 'client' ||
            str_ends_with($project->owner_type, '\\Client')
        ) {
            $client = Client::find($project->owner_id);

            return $client?->email;
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Header Actions
    |--------------------------------------------------------------------------
    */

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
