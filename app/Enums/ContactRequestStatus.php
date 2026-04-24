<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ContactRequestStatus: string implements HasLabel, HasColor
{
    case New = 'new';
    case Forwarded = 'forwarded';
    case Failed = 'failed';
    case Handled = 'handled';

    public function getLabel(): string
    {
        return match ($this) {
            self::New => 'Новая',
            self::Forwarded => 'Отправлена',
            self::Failed => 'Ошибка',
            self::Handled => 'Обработана',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::New => 'warning',
            self::Forwarded => 'info',
            self::Failed => 'danger',
            self::Handled => 'success',
        };
    }
}
