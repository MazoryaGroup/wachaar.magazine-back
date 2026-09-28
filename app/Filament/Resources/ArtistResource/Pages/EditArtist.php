<?php

namespace App\Filament\Resources\ArtistResource\Pages;

use App\Filament\Resources\ArtistResource;
use App\Mail\ArtistApprovedMail;
use App\Mail\ArtistRejectedMail;
use App\Mail\ArtistdraftMail;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;

class EditArtist extends EditRecord
{
    protected static string $resource = ArtistResource::class;

    /*
    |--------------------------------------------------------------------------
    | Previous Status
    |--------------------------------------------------------------------------
    */

    protected ?string $previousStatus = null;

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
    | After Save
    |--------------------------------------------------------------------------
    */

    protected function afterSave(): void
    {
        $artist = $this->record;

        $newStatus = $artist->status;

        /*
        |--------------------------------------------------------------------------
        | Status Did Not Change
        |--------------------------------------------------------------------------
        */

        if ($this->previousStatus === $newStatus) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Get Artist Client
        |--------------------------------------------------------------------------
        */

        $client = $artist->client;

        if (!$client || !$client->email) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Approved
        |--------------------------------------------------------------------------
        */

        if ($newStatus === 'approved') {

            Mail::to($client->email)->send(
                new ArtistApprovedMail($artist)
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Rejected
        |--------------------------------------------------------------------------
        */

        if ($newStatus === 'rejected') {

            Mail::to($client->email)->send(
                new ArtistRejectedMail($artist)
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Pending
        |--------------------------------------------------------------------------
        */

        if ($newStatus === 'pending') {

            Mail::to($client->email)->send(
                new ArtistdraftMail($artist)
            );

            return;
        }
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
