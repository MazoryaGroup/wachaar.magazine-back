<?php

namespace App\Filament\Resources\HomeHeroResource\Pages;

use App\Filament\Resources\HomeHeroResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHomeHeroes extends ListRecords
{
    protected static string $resource = HomeHeroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
