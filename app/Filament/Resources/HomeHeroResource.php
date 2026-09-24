<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomeHeroResource\Pages;
use App\Models\HomeHero;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;

class HomeHeroResource extends Resource
{
    protected static ?string $model = HomeHero::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Hero';

    protected static ?string $modelLabel = 'Hero';

    protected static ?string $pluralModelLabel = 'Hero';

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\FileUpload::make('video')
                ->label('Hero Video')
                ->disk('api_public')
                ->directory('hero')
                ->acceptedFileTypes([
                    'video/mp4',
                    'video/webm',
                ])
                ->maxSize(51200)
                ->downloadable()
                ->openable()
                ->nullable(),

            Forms\Components\FileUpload::make('production_image')
                ->label('Production Image')
                ->image()
                ->disk('api_public')
                ->directory('hero')
                ->acceptedFileTypes([
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                ])
                ->maxSize(5120)
                ->downloadable()
                ->openable()
                ->nullable(),

            Forms\Components\FileUpload::make('magazine_image')
                ->label('Magazine Image')
                ->image()
                ->disk('api_public')
                ->directory('hero')
                ->acceptedFileTypes([
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                ])
                ->maxSize(5120)
                ->downloadable()
                ->openable()
                ->nullable(),

        ]);
    }

    /**
     * فقط یک Hero داریم.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\EditHomeHero::route('/'),
        ];
    }

    /**
     * جلوگیری از ساخت Hero جدید
     */
    public static function canCreate(): bool
    {
        return false;
    }
}
