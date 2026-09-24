<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationLabel = 'Projects';

    protected static ?string $modelLabel = 'Project';

    protected static ?string $pluralModelLabel = 'Projects';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                /*
                |--------------------------------------------------------------------------
                | Project Information
                |--------------------------------------------------------------------------
                */

                Forms\Components\Section::make('Project Information')
                    ->schema([

                        Forms\Components\TextInput::make('client')
                            ->label('Client')
                            ->maxLength(255),

                        Forms\Components\Select::make('type')
                            ->label('Type')
                            ->options([
                                'fashion' => 'Fashion',
                                'commercial' => 'Commercial',
                                'editorial' => 'Editorial',
                                'campaign' => 'Campaign',
                                'film' => 'Film',
                                'other' => 'Other',
                            ])
                            ->searchable(),

                        Forms\Components\Toggle::make('is_marked')
                            ->label('Mark Project')
                            ->default(false)
                            ->inline(false),

                        Forms\Components\DatePicker::make('project_date')
                            ->label('Date')
                            ->native(false),

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
                        | English
                        |--------------------------------------------------------------------------
                        */

                        Forms\Components\Tabs\Tab::make('English')
                            ->schema([

                                Forms\Components\TextInput::make('translations.en.title')
                                    ->label('Title')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('translations.en.subject')
                                    ->label('Subject')
                                    ->maxLength(255),

                                Forms\Components\Textarea::make('translations.en.description')
                                    ->label('Description')
                                    ->rows(5)
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('translations.en.project_description')
                                    ->label('Project Description')
                                    ->rows(6)
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('translations.en.project_cast')
                                    ->label('Project Cast')
                                    ->rows(5)
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('translations.en.campaign_description')
                                    ->label('Campaign Description')
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

                                Forms\Components\TextInput::make('translations.fa.subject')
                                    ->label('موضوع')
                                    ->maxLength(255),

                                Forms\Components\Textarea::make('translations.fa.description')
                                    ->label('توضیحات')
                                    ->rows(5)
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('translations.fa.project_description')
                                    ->label('توضیحات پروژه')
                                    ->rows(6)
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('translations.fa.project_cast')
                                    ->label('عوامل پروژه')
                                    ->rows(5)
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('translations.fa.campaign_description')
                                    ->label('توضیحات کمپین')
                                    ->rows(6)
                                    ->columnSpanFull(),

                            ])
                            ->columns(2),

                    ])
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | Main Media
                |--------------------------------------------------------------------------
                */

                Forms\Components\Section::make('Main Media')
                    ->schema([

                        Forms\Components\FileUpload::make('video')
                            ->label('Project Video')
                            ->disk('api_public')
                            ->directory('projects/videos')
                            ->acceptedFileTypes([
                                'video/mp4',
                                'video/webm',
                                'video/quicktime',
                            ])
                            ->maxSize(204800)
                            ->downloadable()
                            ->openable()
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('cover')
                            ->label('Cover Image / Video')
                            ->disk('api_public')
                            ->directory('projects/covers')
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                                'video/mp4',
                                'video/webm',
                                'video/quicktime',
                            ])
                            ->maxSize(102400)
                            ->downloadable()
                            ->openable()
                            ->columnSpanFull(),

                    ])
                    ->columns(1)
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | Project Gallery
                |--------------------------------------------------------------------------
                */

                Forms\Components\Section::make('Project Gallery')
                    ->schema([

                        Forms\Components\Repeater::make('images')
                            ->relationship()
                            ->label('Project Images')
                            ->schema([

                                Forms\Components\FileUpload::make('image')
                                    ->label('Image')
                                    ->image()
                                    ->disk('api_public')
                                    ->directory('projects/images')
                                    ->acceptedFileTypes([
                                        'image/jpeg',
                                        'image/png',
                                        'image/webp',
                                    ])
                                    ->maxSize(5120)
                                    ->required()
                                    ->imagePreviewHeight('250')
                                    ->downloadable()
                                    ->openable(),

                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Sort Order')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),

                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->reorderable('sort_order')
                            ->collapsible()
                            ->cloneable()
                            ->columnSpanFull(),

                    ])
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | Behind The Scenes
                |--------------------------------------------------------------------------
                */

                Forms\Components\Section::make('Behind The Scenes')
                    ->schema([

                        Forms\Components\FileUpload::make('behind_the_scenes_video')
                            ->label('Behind The Scenes Video')
                            ->disk('api_public')
                            ->directory('projects/behind-the-scenes')
                            ->acceptedFileTypes([
                                'video/mp4',
                                'video/webm',
                                'video/quicktime',
                            ])
                            ->maxSize(204800)
                            ->downloadable()
                            ->openable()
                            ->columnSpanFull(),

                    ])
                    ->columnSpanFull(),

            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('translations.title')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('client')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge(),

                Tables\Columns\IconColumn::make('is_marked')
                    ->label('Marked')
                    ->boolean(),

                Tables\Columns\TextColumn::make('project_date')
                    ->label('Date')
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
            ->defaultSort('project_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
