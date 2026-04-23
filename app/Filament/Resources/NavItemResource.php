<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NavItemResource\Pages;
use App\Models\NavItem;
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
use Illuminate\Support\Facades\Cache;

class NavItemResource extends Resource
{
    protected static ?string $model = NavItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-3-bottom-left';
    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $navigationLabel = 'Меню';
    protected static ?string $modelLabel = 'Пункт меню';
    protected static ?string $pluralModelLabel = 'Пункты меню';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                TextInput::make('label')->label('Подпись')->required()->maxLength(60),
                TextInput::make('anchor')->label('Якорь (#about, /page и т. д.)')->required()->maxLength(200),
                Toggle::make('is_external')->label('Внешняя ссылка (target="_blank")')->inline(false),
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
                TextColumn::make('label')->label('Подпись')->searchable(),
                TextColumn::make('anchor')->label('Якорь')->searchable(),
                IconColumn::make('is_external')->label('↗')->boolean()->toggleable(),
                IconColumn::make('is_active')->label('Показ')->boolean(),
                TextColumn::make('updated_at')->label('Обновлено')->dateTime('d.m.Y H:i')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Активные'),
            ])
            ->actions([EditAction::make()])
            ->bulkActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNavItems::route('/'),
            'create' => Pages\CreateNavItem::route('/create'),
            'edit' => Pages\EditNavItem::route('/{record}/edit'),
        ];
    }

    public static function bustCache(): void
    {
        Cache::forget('settings.nav');
    }
}
