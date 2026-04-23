<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MapPinResource\Pages;
use App\Models\MapPin;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
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

class MapPinResource extends Resource
{
    protected static ?string $model = MapPin::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $navigationLabel = 'Точки на карте';
    protected static ?string $modelLabel = 'Точка на карте';
    protected static ?string $pluralModelLabel = 'Точки на карте';
    protected static ?int $navigationSort = 130;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()
                ->description('Точка располагается одной из 5 предустановленных позиций (c1 - c5). Координаты — это подпись рядом с пином, а не реальные координаты на карте.')
                ->schema([
                    TextInput::make('city_name')->label('Название города')->required()->maxLength(60),
                    TextInput::make('coordinates')->label('Подпись координат (55.75 / 37.62)')->required()->maxLength(60),
                    Select::make('position_class')
                        ->label('Позиция (CSS-класс)')
                        ->options([
                            'c1' => 'c1 — 45% top / 40% left',
                            'c2' => 'c2 — 30% top / 60% left',
                            'c3' => 'c3 — 60% top / 35% left',
                            'c4' => 'c4 — 38% top / 72% left',
                            'c5' => 'c5 — 72% top / 56% left',
                        ])
                        ->required()
                        ->native(false),
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
                TextColumn::make('city_name')->label('Город')->searchable(),
                TextColumn::make('coordinates')->label('Координаты'),
                TextColumn::make('position_class')->label('Позиция')->badge(),
                IconColumn::make('is_active')->label('Показ')->boolean(),
            ])
            ->filters([TernaryFilter::make('is_active')->label('Активные')])
            ->actions([EditAction::make()])
            ->bulkActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMapPins::route('/'),
            'create' => Pages\CreateMapPin::route('/create'),
            'edit' => Pages\EditMapPin::route('/{record}/edit'),
        ];
    }
}
