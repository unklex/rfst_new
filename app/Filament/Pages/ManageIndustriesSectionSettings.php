<?php

namespace App\Filament\Pages;

use App\Settings\IndustriesSectionSettings;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use App\Filament\Concerns\BustsSettingsCache;

class ManageIndustriesSectionSettings extends SettingsPage
{
    use BustsSettingsCache;

    protected static string $settings = IndustriesSectionSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    protected static ?string $navigationGroup = 'Настройки';
    protected static ?string $navigationLabel = 'Отрасли (§ 04) — шапка';
    protected static ?int $navigationSort = 100;
    protected static ?string $title = 'Отрасли — шапка секции';

    public function form(Form $form): Form
    {
        $toolbar = ['bold', 'italic', 'link', 'undo', 'redo'];

        return $form->schema([
            Section::make('Заголовок секции')
                ->description('Строки таблицы — в «Справочники → Отрасли».')
                ->schema([
                    TextInput::make('section_index')->label('Индекс (§ 04)')->required()->maxLength(20),
                    TextInput::make('section_kicker')->label('Подпись возле индекса')->required()->maxLength(60),
                    RichEditor::make('section_heading_html')->label('Заголовок (HTML)')->toolbarButtons($toolbar)->required()->columnSpanFull(),
                    RichEditor::make('section_note_html')->label('Примечание справа (HTML)')->toolbarButtons($toolbar)->required()->columnSpanFull(),
                ])->columns(2),
        ]);
    }

}
