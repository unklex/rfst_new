<?php

namespace App\Filament\Resources\BitrixMockColumnResource\Pages;

use App\Filament\Resources\BitrixMockColumnResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBitrixMockColumns extends ListRecords
{
    protected static string $resource = BitrixMockColumnResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
