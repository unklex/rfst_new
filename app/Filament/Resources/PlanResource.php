<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanResource\Pages;
use App\Models\Plan;
use Filament\Forms\Components\Repeater;
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

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $navigationLabel = 'Тарифы';
    protected static ?string $modelLabel = 'Тариф';
    protected static ?string $pluralModelLabel = 'Тарифы';
    protected static ?int $navigationSort = 110;

    public static function form(Form $form): Form
    {
        $toolbar = ['bold', 'italic', 'link', 'undo', 'redo'];

        return $form->schema([
            Section::make('Шапка тарифа')->schema([
                RichEditor::make('title_html')->label('Название тарифа (HTML)')->toolbarButtons($toolbar)->required()->columnSpanFull(),
                TextInput::make('badge')->label('Бейдж (MVP / хит / enterprise)')->required()->maxLength(40),
            ])->columns(1),

            Section::make('Цена')->schema([
                TextInput::make('price_main')->label('Основная цена (HTML — допустим &thinsp;)')->required()->maxLength(40),
                TextInput::make('price_suffix')->label('Суффикс (₽ / млн ₽)')->required()->maxLength(40),
                TextInput::make('price_caption')->label('Подпись (тыс. · 14 раб. дней)')->required()->maxLength(120),
            ])->columns(3),

            Section::make('Фичи тарифа')
                ->description('Список буллетов — по одному пункту в строке.')
                ->schema([
                    Repeater::make('features')
                        ->label('Пункты')
                        ->schema([
                            TextInput::make('text')->label('Текст пункта')->required()->maxLength(200),
                        ])
                        ->defaultItems(3)
                        ->minItems(1)
                        ->reorderable()
                        ->grid(1)
                        ->afterStateHydrated(function (Repeater $component, $state): void {
                            // Convert flat array of strings from DB → array of ['text' => string] for repeater
                            if (is_array($state) && !empty($state) && !is_array(reset($state))) {
                                $component->state(array_map(fn ($v) => ['text' => (string) $v], array_values($state)));
                            }
                        })
                        ->dehydrateStateUsing(function ($state) {
                            // Flatten ['text' => 'foo'] back to 'foo' strings before persisting
                            if (!is_array($state)) return [];
                            return array_values(array_map(fn ($item) => is_array($item) ? (string) ($item['text'] ?? '') : (string) $item, $state));
                        }),
                ]),

            Section::make('Кнопка и публикация')->schema([
                TextInput::make('cta_label')->label('Кнопка — текст')->required()->maxLength(80),
                TextInput::make('cta_url')->label('Кнопка — URL')->required()->maxLength(300)->default('#contact'),
                Toggle::make('is_highlighted')->label('Подсвечен (тёмный фон)')->inline(false),
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
                TextColumn::make('title_html')->label('Тариф')->html()->wrap(),
                TextColumn::make('badge')->label('Бейдж')->badge(),
                TextColumn::make('price_main')->label('Цена')->html(),
                IconColumn::make('is_highlighted')->label('Подсвечен')->boolean(),
                IconColumn::make('is_active')->label('Показ')->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_highlighted')->label('Подсвечен'),
                TernaryFilter::make('is_active')->label('Активные'),
            ])
            ->actions([EditAction::make()])
            ->bulkActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}
