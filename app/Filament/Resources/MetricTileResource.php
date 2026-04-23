<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MetricTileResource\Pages;
use App\Models\MetricTile;
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

class MetricTileResource extends Resource
{
    protected static ?string $model = MetricTile::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $navigationLabel = 'Плитки метрик';
    protected static ?string $modelLabel = 'Плитка метрики';
    protected static ?string $pluralModelLabel = 'Плитки метрик';
    protected static ?int $navigationSort = 40;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                TextInput::make('key_label')->label('Метка слева (A., B., ...)')->required()->maxLength(10),
                TextInput::make('key_strong')->label('Название (Операции)')->required()->maxLength(80),
                TextInput::make('value_html')->label('Значение (HTML — допустимы <em>, <sup>)')->required()->maxLength(120)->helperText('Пример: <code>2&amp;nbsp;840&lt;sup&gt;+&lt;/sup&gt;</code>'),
                TextInput::make('caption_html')->label('Подпись под значением (HTML)')->required()->maxLength(200)->helperText('Используйте <code>&lt;span&gt;...&lt;/span&gt;</code> для второй строки.'),
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
                TextColumn::make('key_strong')->label('Название')->searchable(),
                TextColumn::make('value_html')->label('Значение')->html(),
                IconColumn::make('is_active')->label('Показ')->boolean(),
            ])
            ->filters([TernaryFilter::make('is_active')->label('Активные')])
            ->actions([EditAction::make()])
            ->bulkActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMetricTiles::route('/'),
            'create' => Pages\CreateMetricTile::route('/create'),
            'edit' => Pages\EditMetricTile::route('/{record}/edit'),
        ];
    }
}
