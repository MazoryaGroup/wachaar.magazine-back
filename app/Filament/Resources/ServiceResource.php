<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationLabel = 'سرویس ';

    protected static ?string $modelLabel = 'سرویس';

    protected static ?string $pluralModelLabel = 'سرویس ها';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                /*
                |--------------------------------------------------------------------------
                | Service Images
                |--------------------------------------------------------------------------
                */

                Forms\Components\Section::make('Service Images')
                    ->schema([

                        Forms\Components\FileUpload::make('image_1')
                            ->label('Image 1')
                            ->image()
                            ->disk('api_public')
                            ->directory('services')
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                            ])
                            ->maxSize(5120)
                            ->imagePreviewHeight('300')
                            ->downloadable()
                            ->openable(),

                        Forms\Components\FileUpload::make('image_2')
                            ->label('Image 2')
                            ->image()
                            ->disk('api_public')
                            ->directory('services')
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                            ])
                            ->maxSize(5120)
                            ->imagePreviewHeight('300')
                            ->downloadable()
                            ->openable(),

                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | Translations
                |--------------------------------------------------------------------------
                */

                Forms\Components\Tabs::make('Translations')
                    ->tabs([

                        /*
                        |--------------------------------------------------------------------------
                        | Persian
                        |--------------------------------------------------------------------------
                        */

                        Forms\Components\Tabs\Tab::make('فارسی')
                            ->schema([

                                Forms\Components\TextInput::make('translation_fa.title')
                                    ->label('عنوان')
                                    ->maxLength(255),

                                Forms\Components\Textarea::make('translation_fa.description')
                                    ->label('توضیحات')
                                    ->rows(6)
                                    ->columnSpanFull(),

                                Forms\Components\Section::make('درباره پکیج')
                                    ->schema([

                                        Forms\Components\TextInput::make(
                                            'translation_fa.about_package_title'
                                        )
                                            ->label('عنوان'),

                                        Forms\Components\Textarea::make(
                                            'translation_fa.about_package_description'
                                        )
                                            ->label('توضیحات')
                                            ->rows(7)
                                            ->columnSpanFull(),

                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),

                                Forms\Components\Section::make('چه چیزهایی شامل می‌شود؟')
                                    ->schema([

                                        Forms\Components\TextInput::make(
                                            'translation_fa.whats_included_title'
                                        )
                                            ->label('عنوان'),

                                        Forms\Components\Textarea::make(
                                            'translation_fa.whats_included_description'
                                        )
                                            ->label('توضیحات')
                                            ->rows(7)
                                            ->columnSpanFull(),

                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),

                            ]),

                        /*
                        |--------------------------------------------------------------------------
                        | English
                        |--------------------------------------------------------------------------
                        */

                        Forms\Components\Tabs\Tab::make('English')
                            ->schema([

                                Forms\Components\TextInput::make('translation_en.title')
                                    ->label('Title')
                                    ->maxLength(255),

                                Forms\Components\Textarea::make('translation_en.description')
                                    ->label('Description')
                                    ->rows(6)
                                    ->columnSpanFull(),

                                Forms\Components\Section::make('About the Package')
                                    ->schema([

                                        Forms\Components\TextInput::make(
                                            'translation_en.about_package_title'
                                        )
                                            ->label('Title'),

                                        Forms\Components\Textarea::make(
                                            'translation_en.about_package_description'
                                        )
                                            ->label('Description')
                                            ->rows(7)
                                            ->columnSpanFull(),

                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),

                                Forms\Components\Section::make("What's Included?")
                                    ->schema([

                                        Forms\Components\TextInput::make(
                                            'translation_en.whats_included_title'
                                        )
                                            ->label('Title'),

                                        Forms\Components\Textarea::make(
                                            'translation_en.whats_included_description'
                                        )
                                            ->label('Description')
                                            ->rows(7)
                                            ->columnSpanFull(),

                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),

                            ]),

                    ])
                    ->columnSpanFull(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([



                Tables\Columns\TextColumn::make('id')
                    ->label('شناسه')
                    ->sortable(),
                Tables\Columns\TextColumn::make('translations.title')
                    ->label('موضوع')
                    ->sortable(),


                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
