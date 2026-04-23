<?php

namespace App\Filament\Pages;

use App\Settings\QuoteSettings;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use App\Filament\Concerns\BustsSettingsCache;

class ManageQuoteSettings extends SettingsPage
{
    use BustsSettingsCache;

    protected static string $settings = QuoteSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';
    protected static ?string $navigationGroup = 'Настройки';
    protected static ?string $navigationLabel = 'Цитата';
    protected static ?int $navigationSort = 150;
    protected static ?string $title = 'Отзыв (цитата)';

    public function form(Form $form): Form
    {
        $toolbar = ['bold', 'italic', 'link', 'undo', 'redo'];

        return $form->schema([
            Section::make('Автор отзыва')->schema([
                TextInput::make('reviewer_name')->label('Имя')->required()->maxLength(80),
                TextInput::make('reviewer_role')->label('Должность')->required()->maxLength(200),
                TextInput::make('reviewer_ref')->label('Референс (отзыв · 2025 · ref / ...)')->required()->maxLength(120),
            ])->columns(2),

            Section::make('Текст цитаты')->schema([
                RichEditor::make('quote_html')->label('Цитата (HTML)')->toolbarButtons($toolbar)->required()->columnSpanFull(),
            ]),

            Section::make('Компания')->schema([
                TextInput::make('company_name')->label('Название компании')->required()->maxLength(120),
                Textarea::make('company_description')->label('Описание (по строкам)')->rows(4)->required(),
            ])->columns(1),
        ]);
    }

}
