<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BitrixMockColumnResource\Pages;
use App\Filament\Resources\BitrixMockColumnResource\RelationManagers\CardsRelationManager;
use App\Models\BitrixMockColumn;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BitrixMockColumnResource extends Resource
{
    protected static ?string $model = BitrixMockColumn::class;

    protected static ?string $navigationIcon = 'heroicon-o-view-columns';
    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $navigationLabel = 'Колонки демо-канбан';
    protected static ?string $modelLabel = 'Колонка канбан';
    protected static ?string $pluralModelLabel = 'Колонки демо-канбан';
    protected static ?int $navigationSort = 70;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                TextInput::make('title')->label('Заголовок колонки (Новые / В работе / Закрыто)')->required()->maxLength(60),
                TextInput::make('badge')->label('Счётчик-бейдж (07)')->required()->maxLength(20),
                TextInput::make('sort')->label('Порядок')->numeric()->default(10)->required(),
                Toggle::make('is_active')->label('Показывать')->default(true)->inline(false),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('sort')->label('#')->sortable(),
                TextColumn::make('title')->label('Заголовок')->searchable(),
                TextColumn::make('badge')->label('Бейдж'),
                TextColumn::make('cards_count')->label('Карточек')->counts('cards'),
                IconColumn::make('is_active')->label('Показ')->boolean(),
            ])
            ->filters([TernaryFilter::make('is_active')->label('Активные')])
            ->actions([EditAction::make()])
            ->bulkActions([DeleteBulkAction::make()]);
    }

    public static function getRelations(): array
    {
        return [
            CardsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBitrixMockColumns::route('/'),
            'create' => Pages\CreateBitrixMockColumn::route('/create'),
            'edit' => Pages\EditBitrixMockColumn::route('/{record}/edit'),
        ];
    }
}
