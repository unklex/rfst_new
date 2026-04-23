<?php

namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use App\Filament\Concerns\BustsSettingsCache;

class ManageGeneralSettings extends SettingsPage
{
    use BustsSettingsCache;

    protected static string $settings = GeneralSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?string $navigationGroup = 'Настройки';
    protected static ?string $navigationLabel = 'Общие';
    protected static ?int $navigationSort = 10;
    protected static ?string $title = 'Общие настройки';

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Идентичность сайта')->schema([
                TextInput::make('site_name')->label('Название сайта')->required()->maxLength(120),
                TextInput::make('tagline')->label('Подзаголовок / слоган')->required()->maxLength(200),
                Textarea::make('meta_description')->label('Meta-description (SEO)')->rows(3)->required()->maxLength(500),
            ])->columns(1),

            Section::make('Бренд')->schema([
                TextInput::make('brand_wordmark')->label('Текстовый логотип')->required()->maxLength(50),
                TextInput::make('brand_wordmark_accent_char')->label('Акцентный символ (курсив)')->required()->maxLength(4)->helperText('Один символ внутри логотипа, который станет курсивным и окрасится в signal-цвет.'),
                TextInput::make('brand_mark_letter')->label('Буква в марке (в рамке)')->required()->maxLength(4),
                TextInput::make('brand_subtitle')->label('Подпись под логотипом')->maxLength(120),
            ])->columns(2),
        ]);
    }

}
