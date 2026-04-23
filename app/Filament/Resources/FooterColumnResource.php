<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FooterColumnResource\Pages;
use App\Filament\Resources\FooterColumnResource\RelationManagers\LinksRelationManager;
use App\Models\FooterColumn;
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

class FooterColumnResource extends Resource
{
    protected static ?string $model = FooterColumn::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $navigationLabel = 'Колонки футера';
    protected static ?string $modelLabel = 'Колонка футера';
    protected static ?string $pluralModelLabel = 'Колонки футера';
    protected static ?int $navigationSort = 140;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                TextInput::make('heading')->label('Заголовок колонки')->required()->maxLength(60),
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
                TextColumn::make('heading')->label('Заголовок')->searchable(),
                TextColumn::make('links_count')->label('Ссылок')->counts('links'),
                IconColumn::make('is_active')->label('Показ')->boolean(),
            ])
            ->filters([TernaryFilter::make('is_active')->label('Активные')])
            ->actions([EditAction::make()])
            ->bulkActions([DeleteBulkAction::make()]);
    }

    public static function getRelations(): array
    {
        return [
            LinksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFooterColumns::route('/'),
            'create' => Pages\CreateFooterColumn::route('/create'),
            'edit' => Pages\EditFooterColumn::route('/{record}/edit'),
        ];
    }
}
