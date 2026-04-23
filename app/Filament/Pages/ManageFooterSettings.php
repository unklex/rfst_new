<?php

namespace App\Filament\Pages;

use App\Settings\FooterSettings;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use App\Filament\Concerns\BustsSettingsCache;

class ManageFooterSettings extends SettingsPage
{
    use BustsSettingsCache;

    protected static string $settings = FooterSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-arrow-down';
    protected static ?string $navigationGroup = 'Настройки';
    protected static ?string $navigationLabel = 'Футер';
    protected static ?int $navigationSort = 180;
    protected static ?string $title = 'Футер';

    public function form(Form $form): Form
    {
        $toolbar = ['bold', 'italic', 'link', 'undo', 'redo'];

        return $form->schema([
            Section::make('О компании и копирайт')
                ->description('Колонки ссылок редактируются в «Справочники → Колонки футера» + связь «Ссылки».')
                ->schema([
                    Textarea::make('about_paragraph')->label('Абзац о компании (слева в футере)')->rows(3)->required(),
                    RichEditor::make('copyright_html')->label('Копирайт-строка (HTML)')->toolbarButtons($toolbar)->required()->columnSpanFull(),
                ])->columns(1),

            Section::make('Юридические ссылки (справа внизу)')->schema([
                TextInput::make('legal_link_policy_label')->label('Политика — текст')->required()->maxLength(60),
                TextInput::make('legal_link_policy_url')->label('Политика — URL')->required()->maxLength(300),
                TextInput::make('legal_link_oferta_label')->label('Оферта — текст')->required()->maxLength(60),
                TextInput::make('legal_link_oferta_url')->label('Оферта — URL')->required()->maxLength(300),
                TextInput::make('legal_link_152fz_label')->label('152-ФЗ — текст')->required()->maxLength(60),
                TextInput::make('legal_link_152fz_url')->label('152-ФЗ — URL')->required()->maxLength(300),
            ])->columns(2),

            Section::make('Массивный вотермарк внизу')->schema([
                TextInput::make('massive_wordmark')->label('Слово')->required()->maxLength(30),
                TextInput::make('massive_italic_char')->label('Курсивный символ внутри')->required()->maxLength(4),
            ])->columns(2),
        ]);
    }

}
