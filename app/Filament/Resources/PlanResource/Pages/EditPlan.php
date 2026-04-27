<?php

namespace App\Filament\Resources\PlanResource\Pages;

use App\Filament\Resources\PlanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPlan extends EditRecord
{
    protected static string $resource = PlanResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['features']) && is_array($data['features'])) {
            $data['features'] = array_values(array_map(
                fn ($i) => is_array($i) ? (string) ($i['text'] ?? '') : (string) $i,
                $data['features']
            ));
        }
        return $data;
    }
}
