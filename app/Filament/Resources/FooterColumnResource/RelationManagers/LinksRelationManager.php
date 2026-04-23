<?php

namespace App\Filament\Resources\FooterColumnResource\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LinksRelationManager extends RelationManager
{
    protected static string $relationship = 'links';

    protected static ?string $title = 'Ссылки в колонке';
    protected static ?string $modelLabel = 'Ссылка';
    protected static ?string $pluralModelLabel = 'Ссылки';

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('label')->label('Подпись')->required()->maxLength(120),
            TextInput::make('url')->label('URL')->required()->maxLength(300),
            Toggle::make('is_external')->label('Внешняя (target="_blank")')->inline(false),
            TextInput::make('sort')->label('Порядок')->numeric()->default(10)->required(),
            Toggle::make('is_active')->label('Показывать')->default(true)->inline(false),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('sort')->label('#'),
                TextColumn::make('label')->label('Подпись')->searchable(),
                TextColumn::make('url')->label('URL')->limit(40),
                IconColumn::make('is_external')->label('↗')->boolean(),
                IconColumn::make('is_active')->label('Показ')->boolean(),
            ])
            ->headerActions([CreateAction::make()])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([DeleteBulkAction::make()]);
    }
}
