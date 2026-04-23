<?php

namespace App\Filament\Pages;

use App\Settings\ServicesSectionSettings;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use App\Filament\Concerns\BustsSettingsCache;

class ManageServicesSectionSettings extends SettingsPage
{
    use BustsSettingsCache;

    protected static string $settings = ServicesSectionSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Настройки';
    protected static ?string $navigationLabel = 'Услуги (§ 02) — шапка';
    protected static ?int $navigationSort = 80;
    protected static ?string $title = 'Услуги — шапка секции';

    public function form(Form $form): Form
    {
        $toolbar = ['bold', 'italic', 'link', 'undo', 'redo'];

        return $form->schema([
            Section::make('Заголовок секции')
                ->description('Сами услуги — в разделе «Справочники → Услуги».')
                ->schema([
                    TextInput::make('section_index')->label('Индекс (§ 02)')->required()->maxLength(20),
                    TextInput::make('section_kicker')->label('Подпись возле индекса')->required()->maxLength(60),
                    RichEditor::make('section_heading_html')->label('Заголовок (HTML)')->toolbarButtons($toolbar)->required()->columnSpanFull(),
                    RichEditor::make('section_note_html')->label('Примечание справа (HTML)')->toolbarButtons($toolbar)->required()->columnSpanFull(),
                ])->columns(2),
        ]);
    }

}
