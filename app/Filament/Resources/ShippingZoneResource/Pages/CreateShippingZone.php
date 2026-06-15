<?php

namespace App\Filament\Resources\ShippingZoneResource\Pages;

use App\Filament\Resources\ShippingZoneResource;
use Filament\Resources\Pages\CreateRecord;

class CreateShippingZone extends CreateRecord
{
    protected static string $resource = ShippingZoneResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
