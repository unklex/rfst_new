<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IndustryResource\Pages;
use App\Models\Industry;
use Filament\Forms\Components\RichEditor;
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

class IndustryResource extends Resource
{
    protected static ?string $model = Industry::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $navigationLabel = 'Отрасли';
    protected static ?string $modelLabel = 'Отрасль';
    protected static ?string $pluralModelLabel = 'Отрасли';
    protected static ?int $navigationSort = 80;

    public static function form(Form $form): Form
    {
        $toolbar = ['bold', 'italic', 'link', 'undo', 'redo'];

        return $form->schema([
            Section::make()->schema([
                TextInput::make('number')->label('Номер строки (01)')->required()->maxLength(8),
                RichEditor::make('title_html')->label('Заголовок (HTML)')->toolbarButtons($toolbar)->required()->columnSpanFull(),
                TextInput::make('subtitle')->label('Подпись / примеры отходов')->required()->maxLength(200),
                TextInput::make('class_codes')->label('Характерные отходы (столбец 3)')->required()->maxLength(120),
                TextInput::make('class_label')->label('Класс (III–IV)')->required()->maxLength(40),
                TextInput::make('class_caption')->label('Подпись под классом')->required()->maxLength(60),
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
                TextColumn::make('title_html')->label('Отрасль')->html()->wrap()->searchable(),
                TextColumn::make('class_codes')->label('Отходы')->toggleable(),
                TextColumn::make('class_label')->label('Класс'),
                IconColumn::make('is_active')->label('Показ')->boolean(),
            ])
            ->filters([TernaryFilter::make('is_active')->label('Активные')])
            ->actions([EditAction::make()])
            ->bulkActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIndustries::route('/'),
            'create' => Pages\CreateIndustry::route('/create'),
            'edit' => Pages\EditIndustry::route('/{record}/edit'),
        ];
    }
}
