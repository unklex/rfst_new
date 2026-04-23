<?php

namespace App\Filament\Pages;

use App\Settings\TopStripSettings;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use App\Filament\Concerns\BustsSettingsCache;

class ManageTopStripSettings extends SettingsPage
{
    use BustsSettingsCache;

    protected static string $settings = TopStripSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-3';
    protected static ?string $navigationGroup = 'Настройки';
    protected static ?string $navigationLabel = 'Верхняя полоса';
    protected static ?int $navigationSort = 30;
    protected static ?string $title = 'Верхняя тёмная полоса';

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Левая часть')->schema([
                TextInput::make('status_text')->label('Статус работы')->required()->maxLength(160),
                TextInput::make('location_text')->label('Город / регион')->required()->maxLength(120),
                TextInput::make('license_text')->label('Лицензия / реквизит')->required()->maxLength(160),
            ])->columns(1),

            Section::make('Правая часть')->schema([
                TextInput::make('lang_label')->label('Переключатель языков')->maxLength(50),
                TextInput::make('telegram_url')->label('Telegram URL')->url()->maxLength(400),
                TextInput::make('whatsapp_url')->label('WhatsApp URL')->url()->maxLength(400),
            ])->columns(1),
        ]);
    }

}
