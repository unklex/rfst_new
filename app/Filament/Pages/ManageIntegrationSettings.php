<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\BustsSettingsCache;
use App\Settings\IntegrationSettings;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class ManageIntegrationSettings extends SettingsPage
{
    use BustsSettingsCache;

    protected static string $settings = IntegrationSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';
    protected static ?string $navigationGroup = 'Настройки';
    protected static ?string $navigationLabel = 'Интеграции';
    protected static ?int $navigationSort = 200;
    protected static ?string $title = 'Интеграции';

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Cloudflare Turnstile (защита от ботов)')
                ->description('Ключи считываются во время рендера — никаких env() и config:cache.')
                ->schema([
                    TextInput::make('turnstile_site_key')
                        ->label('Site key')
                        ->maxLength(100)
                        ->placeholder('0x4AAAA...')
                        ->helperText('Пусто → <script> Turnstile не подключается, форма работает без капчи.'),
                    TextInput::make('turnstile_secret_key')
                        ->label('Secret key')
                        ->password()
                        ->revealable()
                        ->maxLength(200)
                        ->helperText('Шифруется в БД.'),
                ])->columns(2),

            Section::make('Уведомления по заявкам')->schema([
                TextInput::make('notify_email')
                    ->label('Email для копии заявок')
                    ->email()
                    ->maxLength(120)
                    ->helperText('На этот адрес дублируется каждая новая заявка. Пусто → рассылка выключена.'),
            ]),

            Section::make('FastAPI — приёмник заявок')
                ->description('При каждой новой заявке POST с JSON-пейлоадом отправляется на указанный URL после ответа пользователю (dispatch()->afterResponse(), не блокирует форму). Пусто → forward выключен, заявки только сохраняются в админке.')
                ->schema([
                    TextInput::make('fastapi_lead_url')
                        ->label('URL endpoint')
                        ->url()
                        ->maxLength(400)
                        ->placeholder('https://api.example.com/leads'),
                    TextInput::make('fastapi_auth_token')
                        ->label('Bearer-токен (опц.)')
                        ->password()
                        ->revealable()
                        ->maxLength(300)
                        ->helperText('Шифруется в БД. Если указан — добавляется в заголовок Authorization: Bearer.'),
                ])->columns(1),

            Section::make('Sentry (мониторинг ошибок)')
                ->description('DSN из проекта Sentry. Если пусто — SDK работает как no-op, ошибки идут только в storage/logs/laravel.log. Приоритет: это поле → переменная окружения SENTRY_LARAVEL_DSN.')
                ->schema([
                    TextInput::make('sentry_dsn')
                        ->label('DSN')
                        ->maxLength(400)
                        ->placeholder('https://<ключ>@sentry.io/<project-id>'),
                ]),

            Section::make('Яндекс.Метрика')->schema([
                TextInput::make('yandex_metrika_id')
                    ->label('Номер счётчика')
                    ->numeric()
                    ->maxLength(20)
                    ->placeholder('99999999'),
            ]),
        ]);
    }
}
