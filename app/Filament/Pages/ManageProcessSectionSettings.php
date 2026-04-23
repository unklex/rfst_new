<?php

namespace App\Filament\Pages;

use App\Settings\ProcessSectionSettings;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use App\Filament\Concerns\BustsSettingsCache;

class ManageProcessSectionSettings extends SettingsPage
{
    use BustsSettingsCache;

    protected static string $settings = ProcessSectionSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationGroup = 'Настройки';
    protected static ?string $navigationLabel = 'Процесс (§ 06) — шапка';
    protected static ?int $navigationSort = 120;
    protected static ?string $title = 'Процесс — шапка секции';

    public function form(Form $form): Form
    {
        $toolbar = ['bold', 'italic', 'link', 'undo', 'redo'];

        return $form->schema([
            Section::make('Заголовок секции')
                ->description('Шаги процесса — в «Справочники → Этапы процесса».')
                ->schema([
                    TextInput::make('section_index')->label('Индекс (§ 06)')->required()->maxLength(20),
                    TextInput::make('section_kicker')->label('Подпись возле индекса')->required()->maxLength(60),
                    RichEditor::make('section_heading_html')->label('Заголовок (HTML)')->toolbarButtons($toolbar)->required()->columnSpanFull(),
                    RichEditor::make('section_note_html')->label('Примечание справа (HTML)')->toolbarButtons($toolbar)->required()->columnSpanFull(),
                ])->columns(2),
        ]);
    }

}
