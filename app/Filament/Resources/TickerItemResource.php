<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TickerItemResource\Pages;
use App\Models\TickerItem;
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

class TickerItemResource extends Resource
{
    protected static ?string $model = TickerItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-forward';
    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $navigationLabel = 'Бегущая строка';
    protected static ?string $modelLabel = 'Пункт бегущей строки';
    protected static ?string $pluralModelLabel = 'Бегущая строка';
    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                TextInput::make('label')->label('Текст (заглавные буквы в выдаче)')->required()->maxLength(120),
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
                TextColumn::make('label')->label('Текст')->searchable(),
                IconColumn::make('is_active')->label('Показ')->boolean(),
            ])
            ->filters([TernaryFilter::make('is_active')->label('Активные')])
            ->actions([EditAction::make()])
            ->bulkActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickerItems::route('/'),
            'create' => Pages\CreateTickerItem::route('/create'),
            'edit' => Pages\EditTickerItem::route('/{record}/edit'),
        ];
    }
}
