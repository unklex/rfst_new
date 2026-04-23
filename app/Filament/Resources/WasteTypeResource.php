<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WasteTypeResource\Pages;
use App\Models\WasteType;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class WasteTypeResource extends Resource
{
    protected static ?string $model = WasteType::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $navigationLabel = 'Типы отходов';
    protected static ?string $modelLabel = 'Тип отхода';
    protected static ?string $pluralModelLabel = 'Типы отходов';
    protected static ?int $navigationSort = 90;

    public static function form(Form $form): Form
    {
        $toolbar = ['bold', 'italic', 'link', 'undo', 'redo'];

        return $form->schema([
            Section::make('Описание')->schema([
                TextInput::make('fkko_code')->label('Код ФККО (плашка сверху)')->required()->maxLength(80),
                TextInput::make('glyph')->label('Символ-подложка (одна буква/знак)')->required()->maxLength(4),
                RichEditor::make('title_html')->label('Заголовок (HTML)')->toolbarButtons($toolbar)->required()->columnSpanFull(),
                Textarea::make('description')->label('Описание')->rows(3)->required(),
            ])->columns(2),

            Section::make('Классификация')->schema([
                TextInput::make('hazard_class_label')->label('Плашка класса (III класс, IV–V и т. д.)')->required()->maxLength(40),
                Toggle::make('is_hazard')->label('Опасный — оранжевая плашка')->inline(false),
                TextInput::make('sort')->label('Порядок')->numeric()->default(10)->required(),
                Toggle::make('is_active')->label('Показывать')->default(true)->inline(false),
            ])->columns(2),

            Section::make('Изображение (опц.)')
                ->description('Если загружено — перекроет полосатый плейсхолдер на карточке. Авто-WebP: 640px + 320px.')
                ->schema([
                    SpatieMediaLibraryFileUpload::make('image')
                        ->label('Изображение')
                        ->collection('image')
                        ->image()
                        ->imageEditor()
                        ->maxSize(8192)
                        ->conversion('webp'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('sort')->label('#')->sortable(),
                SpatieMediaLibraryImageColumn::make('image')->label('')->collection('image')->conversion('webp_thumb')->square(),
                TextColumn::make('fkko_code')->label('ФККО')->toggleable(),
                TextColumn::make('title_html')->label('Заголовок')->html()->wrap()->searchable(),
                TextColumn::make('hazard_class_label')->label('Класс')->badge()->color(fn (WasteType $r) => $r->is_hazard ? 'warning' : 'gray'),
                IconColumn::make('is_active')->label('Показ')->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_hazard')->label('Опасные'),
                TernaryFilter::make('is_active')->label('Активные'),
            ])
            ->actions([EditAction::make()])
            ->bulkActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWasteTypes::route('/'),
            'create' => Pages\CreateWasteType::route('/create'),
            'edit' => Pages\EditWasteType::route('/{record}/edit'),
        ];
    }
}
