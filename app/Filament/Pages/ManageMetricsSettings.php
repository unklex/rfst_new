<?php

namespace App\Filament\Pages;

use App\Settings\MetricsSettings;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use App\Filament\Concerns\BustsSettingsCache;

class ManageMetricsSettings extends SettingsPage
{
    use BustsSettingsCache;

    protected static string $settings = MetricsSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Настройки';
    protected static ?string $navigationLabel = 'Метрики (шапка)';
    protected static ?int $navigationSort = 70;
    protected static ?string $title = 'Тёмная полоса с метриками';

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Шапка секции метрик')
                ->description('Сами плитки редактируются в разделе «Справочники → Плитки метрик».')
                ->schema([
                    RichEditor::make('header_html')->label('Текст слева (HTML)')->toolbarButtons(['bold', 'italic', 'link', 'undo', 'redo'])->required()->columnSpanFull(),
                    TextInput::make('stamp_text')->label('Штамп справа')->required()->maxLength(80),
                ]),
        ]);
    }

}
