<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MagazineResource\Pages;
use App\Models\Magazine;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MagazineResource extends Resource
{
    protected static ?string $model = Magazine::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'مجله';

    protected static ?string $modelLabel = 'مجله';

    protected static ?string $pluralModelLabel = 'مجله';

    public static function form(Form $form): Form
    {
        return $form->schema([

            /*
            |--------------------------------------------------------------------------
            | Translations
            |--------------------------------------------------------------------------
            */

            Forms\Components\Tabs::make('Translations')
                ->tabs([

                    /*
                    |--------------------------------------------------------------------------
                    | English
                    |--------------------------------------------------------------------------
                    */

                    Forms\Components\Tabs\Tab::make('English')
                        ->schema([

                            Forms\Components\TextInput::make('translations.en.title')
                                ->label('Title')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\Textarea::make('translations.en.description')
                                ->label('Description')
                                ->rows(6)
                                ->columnSpanFull(),

                        ])
                        ->columns(2),

                    /*
                    |--------------------------------------------------------------------------
                    | Persian
                    |--------------------------------------------------------------------------
                    */

                    Forms\Components\Tabs\Tab::make('فارسی')
                        ->schema([

                            Forms\Components\TextInput::make('translations.fa.title')
                                ->label('عنوان')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\Textarea::make('translations.fa.description')
                                ->label('توضیحات')
                                ->rows(6)
                                ->columnSpanFull(),

                        ])
                        ->columns(2),

                ])
                ->columnSpanFull(),

            /*
            |--------------------------------------------------------------------------
            | Magazine Information
            |--------------------------------------------------------------------------
            */

            Forms\Components\DatePicker::make('published_at')
                ->label('Date')
                ->native(false),

            Forms\Components\TextInput::make('pages_count')
                ->label('Pages Count')
                ->numeric()
                ->minValue(1)
                ->maxValue(10000),

            /*
            |--------------------------------------------------------------------------
            | Cover Image
            |--------------------------------------------------------------------------
            */

            Forms\Components\FileUpload::make('cover_image')
                ->label('Cover Image')
                ->image()
                ->disk('api_public')
                ->directory('magazines/covers')
                ->acceptedFileTypes([
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                ])
                ->maxSize(5120)
                ->imagePreviewHeight('300')
                ->downloadable()
                ->openable()
                ->required(),

            /*
            |--------------------------------------------------------------------------
            | PDF
            |--------------------------------------------------------------------------
            */

            Forms\Components\FileUpload::make('pdf')
                ->label('Magazine PDF')
                ->disk('api_public')
                ->directory('magazines/pdf')
                ->acceptedFileTypes([
                    'application/pdf',
                ])
                ->maxSize(202400)
                ->downloadable()
                ->openable()
                ->required(),

            /*
            |--------------------------------------------------------------------------
            | Hashtags
            |--------------------------------------------------------------------------
            */

            Forms\Components\Select::make('hashtags')
                ->label('Hashtags')
                ->relationship(
                    name: 'hashtags',
                    titleAttribute: 'name'
                )
                ->multiple()
                ->searchable()
                ->preload()
                ->createOptionForm([

                    Forms\Components\TextInput::make('name')
                        ->label('Hashtag')
                        ->required()
                        ->maxLength(100),

                    Forms\Components\TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->maxLength(120),

                ])
                ->columnSpanFull(),

        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([



                Tables\Columns\TextColumn::make('translations.title')
                    ->label('موضوع')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pages_count')
                    ->label('تعداد صفحه')
                    ->sortable(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('تاریخ')
                    ->date()
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
            ->defaultSort('published_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMagazines::route('/'),
            'create' => Pages\CreateMagazine::route('/create'),
            'edit' => Pages\EditMagazine::route('/{record}/edit'),
        ];
    }
}
