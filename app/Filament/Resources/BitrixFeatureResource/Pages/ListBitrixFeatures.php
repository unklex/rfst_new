<?php

namespace App\Filament\Resources\BitrixFeatureResource\Pages;

use App\Filament\Resources\BitrixFeatureResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBitrixFeatures extends ListRecords
{
    protected static string $resource = BitrixFeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
