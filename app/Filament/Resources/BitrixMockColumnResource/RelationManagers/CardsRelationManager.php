<?php

namespace App\Filament\Resources\BitrixMockColumnResource\RelationManagers;

use Filament\Forms\Components\Select;
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

class CardsRelationManager extends RelationManager
{
    protected static string $relationship = 'cards';

    protected static ?string $title = 'Карточки сделок';
    protected static ?string $modelLabel = 'Карточка';
    protected static ?string $pluralModelLabel = 'Карточки';

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('label')->label('Заголовок карточки (название клиента)')->required()->maxLength(120),
            TextInput::make('value_html')->label('Значение (HTML)')->required()->maxLength(160),
            Select::make('accent')->label('Акцент')
                ->options([
                    'signal' => 'Signal (оранжевая полоска)',
                    'ink' => 'Ink (чёрная полоска)',
                    'green' => 'Green (зелёная полоска)',
                ])
                ->required()
                ->default('signal')
                ->native(false),
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
                TextColumn::make('label')->label('Клиент')->searchable(),
                TextColumn::make('value_html')->label('Значение')->html(),
                TextColumn::make('accent')->label('Акцент')->badge(),
                IconColumn::make('is_active')->label('Показ')->boolean(),
            ])
            ->headerActions([CreateAction::make()])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([DeleteBulkAction::make()]);
    }
}
