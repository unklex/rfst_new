<?php

namespace App\Filament\Pages;

use App\Settings\NavSettings;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use App\Filament\Concerns\BustsSettingsCache;

class ManageNavSettings extends SettingsPage
{
    use BustsSettingsCache;

    protected static string $settings = NavSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-phone';
    protected static ?string $navigationGroup = 'Настройки';
    protected static ?string $navigationLabel = 'Меню и телефон';
    protected static ?int $navigationSort = 40;
    protected static ?string $title = 'Навигация и телефон';

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Телефон и CTA')->schema([
                TextInput::make('phone_number')->label('Номер телефона')->required()->maxLength(64),
                TextInput::make('phone_label')->label('Подпись над телефоном')->required()->maxLength(60),
                TextInput::make('primary_cta_label')->label('Текст кнопки в меню')->required()->maxLength(60),
            ])->columns(2),
        ]);
    }

}
