<?php

namespace App\Filament\Resources\MapPinResource\Pages;

use App\Filament\Resources\MapPinResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMapPins extends ListRecords
{
    protected static string $resource = MapPinResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
