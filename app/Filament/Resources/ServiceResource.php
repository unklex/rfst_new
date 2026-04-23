<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
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

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $navigationLabel = 'Услуги';
    protected static ?string $modelLabel = 'Услуга';
    protected static ?string $pluralModelLabel = 'Услуги';
    protected static ?int $navigationSort = 50;

    public static function form(Form $form): Form
    {
        $toolbar = ['bold', 'italic', 'link', 'undo', 'redo'];

        return $form->schema([
            Section::make('Основное')->schema([
                TextInput::make('line_label')->label('Метка линии (линия А)')->required()->maxLength(40),
                TextInput::make('index_label')->label('Счётчик (01 / 03)')->required()->maxLength(40),
                TextInput::make('symbol')->label('Символ в квадрате')->required()->maxLength(4),
                RichEditor::make('title_html')->label('Заголовок карточки (HTML)')->toolbarButtons($toolbar)->required()->columnSpanFull(),
                Textarea::make('description')->label('Описание')->rows(4)->required(),
            ])->columns(2),

            Section::make('Технические характеристики (3 строки)')
                ->description('Ровно 3 пары «ключ — значение» отображаются в нижней сетке карточки. HTML разрешён в значениях.')
                ->schema([
                    Repeater::make('spec_rows')
                        ->label('Строки')
                        ->schema([
                            TextInput::make('k')->label('Ключ')->required()->maxLength(40),
                            TextInput::make('v_html')->label('Значение (HTML разрешён)')->required()->maxLength(80),
                        ])
                        ->columns(2)
                        ->defaultItems(3)
                        ->minItems(1)
                        ->maxItems(6)
                        ->reorderable()
                        ->grid(1),
                ]),

            Section::make('Публикация')->schema([
                TextInput::make('footer_code')->label('Код в футере (A-001)')->required()->maxLength(20),
                TextInput::make('cta_url')->label('URL кнопки «читать далее»')->required()->maxLength(300),
                Toggle::make('is_featured')->label('Карточка-featured (оранжевая)')->inline(false),
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
                TextColumn::make('index_label')->label('Счётчик'),
                TextColumn::make('title_html')->label('Заголовок')->html()->wrap()->searchable(),
                TextColumn::make('footer_code')->label('Код')->toggleable(),
                IconColumn::make('is_featured')->label('★')->boolean(),
                IconColumn::make('is_active')->label('Показ')->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_featured')->label('Featured'),
                TernaryFilter::make('is_active')->label('Активные'),
            ])
            ->actions([EditAction::make()])
            ->bulkActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
