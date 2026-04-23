<?php

namespace App\Filament\Resources\FooterColumnResource\Pages;

use App\Filament\Resources\FooterColumnResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFooterColumns extends ListRecords
{
    protected static string $resource = FooterColumnResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
