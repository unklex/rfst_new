<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProcessStepResource\Pages;
use App\Models\ProcessStep;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
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

class ProcessStepResource extends Resource
{
    protected static ?string $model = ProcessStep::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-long-right';
    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $navigationLabel = 'Этапы процесса';
    protected static ?string $modelLabel = 'Этап процесса';
    protected static ?string $pluralModelLabel = 'Этапы процесса';
    protected static ?int $navigationSort = 100;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                TextInput::make('number')->label('Номер (01)')->required()->maxLength(8),
                TextInput::make('title')->label('Название этапа')->required()->maxLength(80),
                Textarea::make('description')->label('Описание')->rows(3)->required(),
                TextInput::make('meta_label')->label('Метка метаданных (время)')->required()->maxLength(40),
                TextInput::make('meta_value')->label('Значение метаданных (≤ 30 мин)')->required()->maxLength(40),
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
                TextColumn::make('number')->label('№'),
                TextColumn::make('title')->label('Название')->searchable(),
                TextColumn::make('meta_value')->label('Время'),
                IconColumn::make('is_active')->label('Показ')->boolean(),
            ])
            ->filters([TernaryFilter::make('is_active')->label('Активные')])
            ->actions([EditAction::make()])
            ->bulkActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProcessSteps::route('/'),
            'create' => Pages\CreateProcessStep::route('/create'),
            'edit' => Pages\EditProcessStep::route('/{record}/edit'),
        ];
    }
}
