<?php

namespace App\Filament\Resources\MapPinResource\Pages;

use App\Filament\Resources\MapPinResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMapPin extends EditRecord
{
    protected static string $resource = MapPinResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
