<?php

namespace App\Filament\Resources\HomeHeroResource\Pages;

use App\Filament\Resources\HomeHeroResource;
use App\Models\HomeHero;
use Filament\Resources\Pages\EditRecord;

class EditHomeHero extends EditRecord
{
    protected static string $resource = HomeHeroResource::class;

    public function mount(int|string $record = 1): void
    {
        parent::mount(1);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
