<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogResource\Pages;
use App\Models\Artist;
use App\Models\Blog;
use App\Models\BlogCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BlogResource extends Resource
{
    protected static ?string $model = Blog::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Blog';



    protected static ?int $navigationSort = 7;

    protected static ?string $modelLabel = 'Blog';

    protected static ?string $pluralModelLabel = 'Blog';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                /*
                |--------------------------------------------------------------------------
                | Basic Information
                |--------------------------------------------------------------------------
                */

                Forms\Components\Section::make('Basic Information')
                    ->schema([

                        Forms\Components\TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (
                                Get $get,
                                Forms\Set $set,
                                ?string $state
                            ) {
                                if (blank($get('slug'))) {
                                    $set('slug', Str::slug($state ?? ''));
                                }
                            }),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(
                                table: 'blogs',
                                column: 'slug',
                                ignoreRecord: true
                            )
                            ->maxLength(255),

                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->relationship(
                                name: 'category',
                                titleAttribute: 'name'
                            )
                            ->searchable()
                            ->preload()
                            ->native(false)

                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Category Name')
                                    ->required()
                                    ->maxLength(150)
                                    ->live(onBlur: true),

                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->maxLength(180)
                                    ->unique(
                                        table: 'blog_categories',
                                        column: 'slug'
                                    ),

                                Forms\Components\Textarea::make('description')
                                    ->label('Description')
                                    ->rows(3)
                                    ->maxLength(500),
                            ])

                            ->createOptionUsing(function (array $data): int {
                                $category = BlogCategory::create([
                                    'name' => trim($data['name']),
                                    'slug' => Str::slug($data['slug']),
                                    'description' => $data['description'] ?? null,
                                ]);

                                return $category->getKey();
                            })

                            ->editOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Category Name')
                                    ->required()
                                    ->maxLength(150),

                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->maxLength(180)
                                    ->unique(
                                        table: 'blog_categories',
                                        column: 'slug',
                                        ignoreRecord: true
                                    ),

                                Forms\Components\Textarea::make('description')
                                    ->label('Description')
                                    ->rows(3)
                                    ->maxLength(500),
                            ]),

                        Forms\Components\Textarea::make('excerpt')
                            ->label('Excerpt')
                            ->rows(4)
                            ->maxLength(1000)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('featured_image')
                            ->label('Featured Image')
                            ->image()
                            ->disk('public')
                            ->directory('blogs/featured')
                            ->imageEditor()
                            ->maxSize(5120)
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                            ])
                            ->columnSpanFull(),

                    ])
                    ->columns(2),

                /*
                |--------------------------------------------------------------------------
                | Author
                |--------------------------------------------------------------------------
                */

                Forms\Components\Section::make('Author')
                    ->schema([

                        Forms\Components\Select::make('author_type')
                            ->label('Published By')
                            ->options([
                                'website' => 'Wachaar Website',
                                'artist' => 'Artist',
                            ])
                            ->default('website')
                            ->required()
                            ->live()
                            ->native(false),

                        Forms\Components\Select::make('author_id')
                            ->label('Artist')
                            ->options(function () {
                                return Artist::query()
                                    ->orderBy('first_name')
                                    ->orderBy('last_name')
                                    ->get()
                                    ->mapWithKeys(function (Artist $artist) {
                                        return [
                                            $artist->id =>
                                                trim(
                                                    $artist->first_name .
                                                    ' ' .
                                                    $artist->last_name
                                                ),
                                        ];
                                    });
                            })
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->visible(fn (Get $get): bool =>
                                $get('author_type') === 'artist'
                            )
                            ->required(fn (Get $get): bool =>
                                $get('author_type') === 'artist'
                            ),

                    ])
                    ->columns(2),

                /*
                |--------------------------------------------------------------------------
                | Publishing
                |--------------------------------------------------------------------------
                */

                Forms\Components\Section::make('Publishing')
                    ->schema([

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                            ])
                            ->default('draft')
                            ->required()
                            ->native(false),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Published At')
                            ->seconds(false),

                        Forms\Components\TextInput::make('reading_time')
                            ->label('Reading Time')
                            ->numeric()
                            ->minValue(1)
                            ->suffix('min'),

                    ])
                    ->columns(3),

                /*
                |--------------------------------------------------------------------------
                | Article Content
                |--------------------------------------------------------------------------
                */

                Forms\Components\Section::make('Article Content')
                    ->schema([

                        Forms\Components\RichEditor::make('content')
                            ->label('Content')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'link',
                                'blockquote',
                                'bulletList',
                                'orderedList',
                                'h2',
                                'h3',
                                'undo',
                                'redo',
                            ])
                            ->columnSpanFull(),

                    ]),

                /*
                |--------------------------------------------------------------------------
                | Blog Gallery
                |--------------------------------------------------------------------------
                */

                Forms\Components\Section::make('Blog Gallery')
                    ->schema([

                        Forms\Components\Repeater::make('images')
                            ->relationship('images')
                            ->label('Gallery Images')
                            ->schema([

                                Forms\Components\FileUpload::make('image')
                                    ->label('Image')
                                    ->image()
                                    ->disk('public')
                                    ->directory('blogs/gallery')
                                    ->imageEditor()
                                    ->maxSize(5120)
                                    ->acceptedFileTypes([
                                        'image/jpeg',
                                        'image/png',
                                        'image/webp',
                                    ])
                                    ->required()
                                    ->columnSpanFull(),

                                Forms\Components\Hidden::make('sort_order')
                                    ->default(0),

                            ])
                            ->reorderable('sort_order')
                            ->collapsible()
                            ->cloneable()
                            ->itemLabel(
                                fn (array $state): ?string =>
                                isset($state['image'])
                                    ? basename($state['image'])
                                    : 'Gallery Image'
                            )
                            ->defaultItems(0)
                            ->columnSpanFull(),

                    ]),

                /*
                |--------------------------------------------------------------------------
                | SEO
                |--------------------------------------------------------------------------
                */

                Forms\Components\Section::make('SEO')
                    ->schema([

                        Forms\Components\TextInput::make('meta_title')
                            ->label('Meta Title')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('meta_description')
                            ->label('Meta Description')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('meta_keywords')
                            ->label('Meta Keywords')
                            ->rows(2)
                            ->maxLength(1000)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('canonical_url')
                            ->label('Canonical URL')
                            ->url()
                            ->maxLength(500)
                            ->columnSpanFull(),

                    ])
                    ->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\ImageColumn::make('featured_image')
                    ->label('Image')
                    ->disk('public')
                    ->square(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('author_name')
                    ->label('Author')
                    ->state(fn (Blog $record): string =>
                    $record->author_name
                    ),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'published',
                    ]),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Published At')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('views')
                    ->label('Views')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ]),

                Tables\Filters\SelectFilter::make('author_type')
                    ->label('Published By')
                    ->options([
                        'website' => 'Wachaar Website',
                        'artist' => 'Artist',
                    ]),

                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),

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
            'index' => Pages\ListBlogs::route('/'),
            'create' => Pages\CreateBlog::route('/create'),
            'edit' => Pages\EditBlog::route('/{record}/edit'),
        ];
    }
}
