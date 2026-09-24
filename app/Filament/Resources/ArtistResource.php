<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArtistResource\Pages;
use App\Models\Artist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;

class ArtistResource extends Resource
{
    protected static ?string $model = Artist::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Artists';


    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                // ========================================
                // Artist Information
                // ========================================
                Section::make('Artist Information')
                    ->schema([
                        TextInput::make('first_name')
                            ->label('First Name')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('last_name')
                            ->label('Last Name')
                            ->required()
                            ->maxLength(100),

                        FileUpload::make('profile_image')
                            ->label('Profile Image')
                            ->image()
                            ->disk('public')
                            ->directory('artists/profile')
                            ->visibility('public')
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                            ])
                            ->maxSize(5120)
                            ->imagePreviewHeight('200')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                // ========================================
                // Social Media
                // ========================================
                Section::make('Social Media')
                    ->schema([
                        TextInput::make('facebook_url')
                            ->label('Facebook URL')
                            ->url()
                            ->maxLength(500)
                            ->placeholder('https://facebook.com/...'),

                        TextInput::make('instagram_url')
                            ->label('Instagram URL')
                            ->url()
                            ->maxLength(500)
                            ->placeholder('https://instagram.com/...'),

                        TextInput::make('youtube_url')
                            ->label('YouTube URL')
                            ->url()
                            ->maxLength(500)
                            ->placeholder('https://youtube.com/...'),
                    ])
                    ->columns(3),

                // ========================================
                // Content 1
                // ========================================
                Section::make('Content 1')
                    ->schema([
                        TextInput::make('title_1')
                            ->label('Title 1')
                            ->maxLength(255),

                        Textarea::make('description_1')
                            ->label('Description 1')
                            ->rows(5)
                            ->maxLength(10000)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                // ========================================
                // Content 2
                // ========================================
                Section::make('Content 2')
                    ->schema([
                        TextInput::make('title_2')
                            ->label('Title 2')
                            ->maxLength(255),

                        Textarea::make('description_2')
                            ->label('Description 2')
                            ->rows(5)
                            ->maxLength(10000)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                // ========================================
                // Portfolio Gallery
                // ========================================
                Section::make('Portfolio Gallery')
                    ->description('Add images and videos to the artist portfolio.')
                    ->schema([
                        Repeater::make('portfolios')
                            ->relationship()
                            ->label('Portfolio Items')
                            ->schema([

                                Select::make('type')
                                    ->label('Media Type')
                                    ->options([
                                        'image' => 'Image',
                                        'video' => 'Video',
                                    ])
                                    ->required()
                                    ->live()
                                    ->default('image'),

                                FileUpload::make('file')
                                    ->label('File')
                                    ->disk('public')
                                    ->directory('artists/portfolio')
                                    ->visibility('public')
                                    ->required()
                                    ->maxSize(100 * 1024)
                                    ->acceptedFileTypes(function (Forms\Get $get) {
                                        return $get('type') === 'video'
                                            ? [
                                                'video/mp4',
                                                'video/webm',
                                                'video/quicktime',
                                            ]
                                            : [
                                                'image/jpeg',
                                                'image/png',
                                                'image/webp',
                                            ];
                                    })
                                    ->image(fn (Forms\Get $get) => $get('type') === 'image')
                                    ->openable()
                                    ->downloadable(),

                                TextInput::make('sort_order')
                                    ->label('Sort Order')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                            ])
                            ->columns(3)
                            ->reorderable('sort_order')
                            ->orderColumn('sort_order')
                            ->addActionLabel('Add Portfolio Item')
                            ->collapsible()
                            ->itemLabel(function (array $state): ?string {
                                if (!empty($state['type'])) {
                                    return ucfirst($state['type']);
                                }

                                return 'Portfolio Item';
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                ImageColumn::make('profile_image')
                    ->label('Profile')
                    ->disk('public')
                    ->circular(),

                TextColumn::make('first_name')
                    ->label('First Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('last_name')
                    ->label('Last Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('instagram_url')
                    ->label('Instagram')
                    ->limit(30),

                TextColumn::make('facebook_url')
                    ->label('Facebook')
                    ->limit(30),

                TextColumn::make('youtube_url')
                    ->label('YouTube')
                    ->limit(30),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArtists::route('/'),
            'create' => Pages\CreateArtist::route('/create'),
            'edit' => Pages\EditArtist::route('/{record}/edit'),
        ];
    }
}
