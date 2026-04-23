<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BitrixFeatureResource\Pages;
use App\Models\BitrixFeature;
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

class BitrixFeatureResource extends Resource
{
    protected static ?string $model = BitrixFeature::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube-transparent';
    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $navigationLabel = 'Возможности Битрикс24';
    protected static ?string $modelLabel = 'Возможность';
    protected static ?string $pluralModelLabel = 'Возможности Битрикс24';
    protected static ?int $navigationSort = 60;

    public static function form(Form $form): Form
    {
        $toolbar = ['bold', 'italic', 'link', 'undo', 'redo'];

        return $form->schema([
            Section::make()->schema([
                TextInput::make('number')->label('Номер (A.01)')->required()->maxLength(20),
                RichEditor::make('title_html')->label('Заголовок (HTML)')->toolbarButtons($toolbar)->required()->columnSpanFull(),
                TextInput::make('subtitle')->label('Подпись под заголовком')->required()->maxLength(200),
                TextInput::make('url')->label('URL')->required()->maxLength(300)->default('#'),
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
                TextColumn::make('number')->label('Номер'),
                TextColumn::make('title_html')->label('Заголовок')->html()->wrap(),
                TextColumn::make('subtitle')->label('Подпись')->limit(50)->toggleable(),
                IconColumn::make('is_active')->label('Показ')->boolean(),
            ])
            ->filters([TernaryFilter::make('is_active')->label('Активные')])
            ->actions([EditAction::make()])
            ->bulkActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBitrixFeatures::route('/'),
            'create' => Pages\CreateBitrixFeature::route('/create'),
            'edit' => Pages\EditBitrixFeature::route('/{record}/edit'),
        ];
    }
}
