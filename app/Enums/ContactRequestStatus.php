<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ContactRequestStatus: string implements HasLabel, HasColor
{
    case New = 'new';
    case Handled = 'handled';

    public function getLabel(): string
    {
        return match ($this) {
            self::New => 'Новая',
            self::Handled => 'Обработана',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::New => 'warning',
            self::Handled => 'success',
        };
    }
}
