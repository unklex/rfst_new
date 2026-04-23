<?php

namespace App\Filament\Pages;

use App\Settings\DesignSettings;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use App\Filament\Concerns\BustsSettingsCache;

class ManageDesignSettings extends SettingsPage
{
    use BustsSettingsCache;

    protected static string $settings = DesignSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-swatch';
    protected static ?string $navigationGroup = 'Настройки';
    protected static ?string $navigationLabel = 'Палитра';
    protected static ?int $navigationSort = 20;
    protected static ?string $title = 'Палитра и типографика';

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Переключатели темы (атрибуты data-* на <body>)')
                ->description('Каждая комбинация меняет переменные CSS без пересборки ассетов.')
                ->schema([
                    Select::make('signal')
                        ->label('Акцентный цвет')
                        ->options([
                            'hazard' => 'Hazard — оранжевый',
                            'iron' => 'Iron — графит',
                            'indigo' => 'Indigo — синий',
                            'blood' => 'Blood — бордо',
                        ])
                        ->required()
                        ->native(false),
                    Select::make('paper')
                        ->label('Фон')
                        ->options([
                            'bone' => 'Bone — кремовый (по умолчанию)',
                            'fog' => 'Fog — серый',
                            'noir' => 'Noir — тёмный',
                        ])
                        ->required()
                        ->native(false),
                    Select::make('head_weight')
                        ->label('Заголовки')
                        ->options([
                            'serif' => 'Serif — Plex Serif',
                            'sans' => 'Sans — Plex Sans',
                        ])
                        ->required()
                        ->native(false),
                ])->columns(3),
        ]);
    }

}
