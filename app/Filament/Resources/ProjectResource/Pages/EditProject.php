<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Mail\ProjectApprovedMail;
use App\Mail\ProjectdraftMail;
use App\Mail\ProjectRejectedMail;
use App\Models\Artist;
use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectTranslation;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected ?string $previousStatus = null;

    protected array $projectTranslations = [];

    protected array $galleryImages = [];

    /*
    |--------------------------------------------------------------------------
    | Before Save
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

        foreach (['en', 'fa'] as $locale) {
            $translation = $translations->get($locale);

            $data['translations'][$locale] = [
                'title' => $translation?->title,
                'subject' => $translation?->subject,
                'description' => $translation?->description,
                'project_description' => $translation?->project_description,
                'project_cast' => $translation?->project_cast,
                'campaign_description' => $translation?->campaign_description,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Load Existing Gallery Images
        |--------------------------------------------------------------------------
        */

        $data['gallery_uploads'] = $this->record
            ->images()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('image')
            ->filter()
            ->values()
            ->all();

        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | Prepare Data Before Save
    |--------------------------------------------------------------------------
    */

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->projectTranslations = $data['translations'] ?? [];

        $this->galleryImages = array_values(
            array_filter(
                $data['gallery_uploads'] ?? [],
                fn ($path) => is_string($path) && $path !== ''
            )
        );

        unset(
            $data['translations'],
            $data['gallery_uploads'],
            $data['gallery_images']
        );

        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | After Save
    |--------------------------------------------------------------------------
    */

    protected function afterSave(): void
    {
        $project = $this->record;

        /*
        |--------------------------------------------------------------------------
        | Save Translations
        |--------------------------------------------------------------------------
        */

        foreach (['en', 'fa'] as $locale) {
            $translation = $this->projectTranslations[$locale] ?? null;

            if (!is_array($translation)) {
                continue;
            }

            ProjectTranslation::updateOrCreate(
                [
                    'project_id' => $project->id,
                    'locale' => $locale,
                ],
                [
                    'title' => $translation['title'] ?? null,
                    'subject' => $translation['subject'] ?? null,
                    'description' => $translation['description'] ?? null,
                    'project_description' =>
                        $translation['project_description'] ?? null,
                    'project_cast' =>
                        $translation['project_cast'] ?? null,
                    'campaign_description' =>
                        $translation['campaign_description'] ?? null,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sync Gallery Order
        |--------------------------------------------------------------------------
        */

        $existingImages = $project
            ->images()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $existingByPath = $existingImages->keyBy('image');

        $position = 1;
        $processedPaths = [];

        foreach ($this->galleryImages as $path) {
            if (in_array($path, $processedPaths, true)) {
                continue;
            }

            $processedPaths[] = $path;

            $existingImage = $existingByPath->get($path);

            if ($existingImage) {
                $existingImage->update([
                    'sort_order' => $position,
                ]);
            } else {
                $project->images()->create([
                    'image' => $path,
                    'sort_order' => $position,
                ]);
            }

            $position++;
        }

        /*
        |--------------------------------------------------------------------------
        | Preserve Existing Images Not In Submitted Gallery
        |--------------------------------------------------------------------------
        |
        | Safety: do not delete existing image records or files.
        | Images omitted from the form are appended to the gallery.
        |
        */

        foreach ($existingImages as $image) {
            if (in_array($image->image, $processedPaths, true)) {
                continue;
            }

            $image->update([
                'sort_order' => $position,
            ]);

            $position++;
        }

        Log::info('Wachaar project gallery updated', [
            'project_id' => $project->id,
            'gallery_count' => $position - 1,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Status Email
        |--------------------------------------------------------------------------
        */

        $newStatus = $project->status;

        if ($this->previousStatus === $newStatus) {
            return;
        }

        $email = $this->getProjectOwnerEmail($project);

        if (!$email) {
            return;
        }

        try {
            if ($newStatus === 'draft') {
                Mail::to($email)->send(
                    new ProjectdraftMail($project)
                );
            } elseif ($newStatus === 'approved') {
                Mail::to($email)->send(
                    new ProjectApprovedMail($project)
                );
            } elseif ($newStatus === 'rejected') {
                Mail::to($email)->send(
                    new ProjectRejectedMail($project)
                );
            }
        } catch (\Throwable $e) {
            Log::error('Wachaar project status email failed', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get Project Owner Email
    |--------------------------------------------------------------------------
    */

    private function getProjectOwnerEmail(Project $project): ?string
    {
        if (
            empty($project->owner_type) ||
            empty($project->owner_id)
        ) {
            return null;
        }

        if (
            $project->owner_type === Artist::class ||
            $project->owner_type === 'artist' ||
            str_ends_with($project->owner_type, '\\Artist')
        ) {
            $artist = Artist::with('client')
                ->find($project->owner_id);

            return $artist?->client?->email;
        }

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
            Actions\DeleteAction::make(),
        ];
    }
}
