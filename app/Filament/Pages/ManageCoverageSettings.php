<?php

namespace App\Filament\Pages;

use App\Settings\CoverageSettings;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use App\Filament\Concerns\BustsSettingsCache;

class ManageCoverageSettings extends SettingsPage
{
    use BustsSettingsCache;

    protected static string $settings = CoverageSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationGroup = 'Настройки';
    protected static ?string $navigationLabel = 'География (§ 08)';
    protected static ?int $navigationSort = 140;
    protected static ?string $title = 'География покрытия — § 08';

    public function form(Form $form): Form
    {
        $toolbar = ['bold', 'italic', 'link', 'undo', 'redo'];

        return $form->schema([
            Section::make('Тексты секции')
                ->description('Список регионов и точки на карте — в «Справочники → Регионы» и «Точки на карте».')
                ->schema([
                    TextInput::make('kicker')->label('Подпись (§ 08 · география)')->required()->maxLength(120),
                    RichEditor::make('heading_html')->label('Заголовок (HTML)')->toolbarButtons($toolbar)->required()->columnSpanFull(),
                    Textarea::make('paragraph')->label('Абзац под заголовком')->rows(3)->required(),
                    TextInput::make('map_meta_html')->label('Надпись на карте (HTML)')->required()->maxLength(200),
                ])->columns(1),
        ]);
    }

}
