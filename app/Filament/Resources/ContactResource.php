<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactResource\Pages;
use App\Models\Contact;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $modelLabel = 'تماس باما
   ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('service')
                    ->maxLength(50),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('company_name')
                    ->email()
                    ->maxLength(255),
                Forms\Components\Textarea::make('message')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->label('شناسه'),
                Tables\Columns\TextColumn::make('name')->sortable()->searchable()->label('نام مشتری'),
                Tables\Columns\TextColumn::make('service')->sortable()->searchable()->label('خدمات'),
                Tables\Columns\TextColumn::make('company_name')->sortable()->searchable()->label('تام شرکت'),
                Tables\Columns\TextColumn::make('email')->sortable()->searchable()->label('ایمیل'),
                Tables\Columns\TextColumn::make('message')->limit(50)->label('پیام'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
//                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContact::route('/'),
            'create' => Pages\CreateContact::route('/create'),
            'edit' => Pages\EditContact::route('/{record}/edit'),

        ];
    }
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'message' => $this->message,

            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
        }
}
