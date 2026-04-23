<?php

namespace App\Filament\Pages;

use App\Settings\AboutSettings;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use App\Filament\Concerns\BustsSettingsCache;

class ManageAboutSettings extends SettingsPage
{
    use BustsSettingsCache;

    protected static string $settings = AboutSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Настройки';
    protected static ?string $navigationLabel = 'О компании (§ 01)';
    protected static ?int $navigationSort = 60;
    protected static ?string $title = 'О компании — § 01';

    public function form(Form $form): Form
    {
        $toolbar = ['bold', 'italic', 'link', 'undo', 'redo'];

        return $form->schema([
            Section::make('Шапка секции')->schema([
                TextInput::make('section_index')->label('Индекс (§ 01)')->required()->maxLength(20),
                TextInput::make('section_kicker')->label('Подпись рядом с индексом')->required()->maxLength(60),
                RichEditor::make('section_heading_html')->label('Заголовок секции (HTML)')->toolbarButtons($toolbar)->required()->columnSpanFull(),
                RichEditor::make('legal_block_html')->label('Блок реквизитов (HTML)')->toolbarButtons($toolbar)->required()->columnSpanFull(),
            ])->columns(2),

            Section::make('Тело')->schema([
                RichEditor::make('body_heading_html')->label('Подзаголовок (HTML)')->toolbarButtons($toolbar)->required()->columnSpanFull(),
                Textarea::make('body_paragraph')->label('Основной абзац')->rows(5)->required(),
                TextInput::make('cta_label')->label('Кнопка — текст')->required()->maxLength(80),
                TextInput::make('cta_url')->label('Кнопка — URL')->required()->maxLength(300),
            ])->columns(1),
        ]);
    }

}
