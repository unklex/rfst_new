<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteAssetResource\Pages;
use App\Models\SiteAsset;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SiteAssetResource extends Resource
{
    protected static ?string $model = SiteAsset::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $navigationLabel = 'Медиа-файлы сайта';
    protected static ?string $modelLabel = 'Медиа-файл';
    protected static ?string $pluralModelLabel = 'Медиа-файлы сайта';
    protected static ?int $navigationSort = 150;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Идентификация')
                ->description('Ключ (hero_bg, about_archive, quote_reviewer, favicon, og_image) определяет, где файл появится на сайте. Не меняйте ключ без необходимости.')
                ->schema([
                    TextInput::make('key')->label('Ключ')->required()->maxLength(60)->disabled(fn ($record) => $record !== null),
                    TextInput::make('title')->label('Название (для админки)')->required()->maxLength(120),
                    TextInput::make('alt')->label('Alt-текст')->maxLength(200)->helperText('Для SEO / доступности. Оставьте пустым для декоративных изображений.'),
                ])->columns(1),

            Section::make('Файл')
                ->description('Авто-конверсия в WebP с размером под ключ. favicon сохраняется в 180px, hero/og — 1920px, остальное — 1280px. Мобильный вариант 720px для всех кроме favicon.')
                ->schema([
                    SpatieMediaLibraryFileUpload::make('image')
                        ->label('Изображение')
                        ->collection('image')
                        ->image()
                        ->imageEditor()
                        ->maxSize(10240)
                        ->conversion('webp'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('key')
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')->label('')->collection('image')->conversion('webp')->square(),
                TextColumn::make('key')->label('Ключ')->badge()->searchable(),
                TextColumn::make('title')->label('Название')->searchable(),
                TextColumn::make('alt')->label('Alt')->limit(40)->toggleable(),
                TextColumn::make('updated_at')->label('Обновлено')->dateTime('d.m.Y H:i'),
            ])
            ->actions([EditAction::make()]);
    }

    public static function canCreate(): bool
    {
        // Keys are seeded; editors upload files — they don't add new keys.
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSiteAssets::route('/'),
            'edit' => Pages\EditSiteAsset::route('/{record}/edit'),
        ];
    }
}
