<?php

namespace App\Filament\Pages;

use App\Settings\LegalSettings;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use App\Filament\Concerns\BustsSettingsCache;

class ManageLegalSettings extends SettingsPage
{
    use BustsSettingsCache;

    protected static string $settings = LegalSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationGroup = 'Настройки';
    protected static ?string $navigationLabel = 'Юр. реквизиты';
    protected static ?int $navigationSort = 190;
    protected static ?string $title = 'Юридические реквизиты';

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Организация')->schema([
                TextInput::make('legal_name')->label('Полное наименование')->required()->maxLength(200),
                TextInput::make('inn')->label('ИНН')->required()->maxLength(20),
                TextInput::make('kpp')->label('КПП')->maxLength(20),
                TextInput::make('ogrn')->label('ОГРН')->maxLength(20),
            ])->columns(2),

            Section::make('Лицензия')->schema([
                TextInput::make('license_number')->label('Номер лицензии')->required()->maxLength(120),
                TextInput::make('license_issuer')->label('Кто выдал')->required()->maxLength(160),
                TextInput::make('license_date')->label('Дата выдачи')->required()->maxLength(60),
            ])->columns(3),
        ]);
    }

}
