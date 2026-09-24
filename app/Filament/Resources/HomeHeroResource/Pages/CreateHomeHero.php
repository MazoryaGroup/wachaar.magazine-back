<?php

namespace App\Filament\Resources\HomeHeroResource\Pages;

use App\Filament\Resources\HomeHeroResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateHomeHero extends CreateRecord
{
    protected static string $resource = HomeHeroResource::class;
}
