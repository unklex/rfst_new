<?php

namespace App\Filament\Pages;

use App\Settings\ContactSettings;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use App\Filament\Concerns\BustsSettingsCache;

class ManageContactSettings extends SettingsPage
{
    use BustsSettingsCache;

    protected static string $settings = ContactSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Настройки';
    protected static ?string $navigationLabel = 'Контактная форма';
    protected static ?int $navigationSort = 170;
    protected static ?string $title = 'Контактная форма и реквизиты';

    public function form(Form $form): Form
    {
        $toolbar = ['bold', 'italic', 'link', 'undo', 'redo'];

        return $form->schema([
            Section::make('Заголовок и контакты')->schema([
                RichEditor::make('heading_html')->label('Заголовок секции (HTML)')->toolbarButtons($toolbar)->required()->columnSpanFull(),
                TextInput::make('address')->label('Адрес')->required()->maxLength(200),
                TextInput::make('phone')->label('Телефон')->required()->maxLength(64),
                TextInput::make('email')->label('E-mail')->email()->required()->maxLength(120),
                TextInput::make('hours')->label('Часы работы')->required()->maxLength(120),
                TextInput::make('messengers')->label('Мессенджеры')->required()->maxLength(120),
            ])->columns(2),

            Section::make('Подписи полей формы')->schema([
                TextInput::make('form_label_name')->label('Лейбл — Имя')->required()->maxLength(80),
                TextInput::make('form_label_phone')->label('Лейбл — Телефон')->required()->maxLength(80),
                TextInput::make('form_label_email')->label('Лейбл — E-mail')->required()->maxLength(80),
                TextInput::make('form_label_message')->label('Лейбл — Сообщение')->required()->maxLength(80),
            ])->columns(2),

            Section::make('Плейсхолдеры полей')->schema([
                TextInput::make('form_placeholder_name')->label('Плейсхолдер — Имя')->required()->maxLength(120),
                TextInput::make('form_placeholder_phone')->label('Плейсхолдер — Телефон')->required()->maxLength(80),
                TextInput::make('form_placeholder_email')->label('Плейсхолдер — E-mail')->required()->maxLength(120),
                TextInput::make('form_placeholder_message')->label('Плейсхолдер — Сообщение')->required()->maxLength(200),
            ])->columns(2),

            Section::make('Кнопка и согласие')->schema([
                TextInput::make('form_submit_label')->label('Кнопка «Отправить»')->required()->maxLength(80),
                Textarea::make('form_consent_text')->label('Короткий текст под формой')->rows(2)->required(),
                Textarea::make('personal_data_consent_text')->label('Полный текст согласия 152-ФЗ')->rows(5)->required()->helperText('Хеш этого текста сохраняется в каждой заявке — для юридической прослеживаемости.'),
            ])->columns(1),
        ]);
    }

}
