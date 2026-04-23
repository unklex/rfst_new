<?php

namespace App\Filament\Pages;

use App\Settings\CtaBandSettings;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use App\Filament\Concerns\BustsSettingsCache;

class ManageCtaBandSettings extends SettingsPage
{
    use BustsSettingsCache;

    protected static string $settings = CtaBandSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationGroup = 'Настройки';
    protected static ?string $navigationLabel = 'CTA-полоса';
    protected static ?int $navigationSort = 160;
    protected static ?string $title = 'Оранжевая полоса с CTA';

    public function form(Form $form): Form
    {
        $toolbar = ['bold', 'italic', 'link', 'undo', 'redo'];

        return $form->schema([
            Section::make('Оранжевая полоса — текст')->schema([
                RichEditor::make('heading_html')->label('Заголовок (HTML)')->toolbarButtons($toolbar)->required()->columnSpanFull(),
                Textarea::make('paragraph')->label('Абзац')->rows(3)->required(),
            ]),

            Section::make('Кнопки')->schema([
                TextInput::make('cta_primary_label')->label('Основная — текст')->required()->maxLength(80),
                TextInput::make('cta_primary_url')->label('Основная — URL')->required()->maxLength(300),
                TextInput::make('cta_secondary_label')->label('Вторая — текст')->required()->maxLength(80),
                TextInput::make('cta_secondary_url')->label('Вторая — URL')->required()->maxLength(300),
            ])->columns(2),
        ]);
    }

}
