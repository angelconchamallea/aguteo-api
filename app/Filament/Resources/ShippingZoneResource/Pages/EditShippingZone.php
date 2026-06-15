<?php

namespace App\Filament\Resources\ShippingZoneResource\Pages;

use App\Filament\Resources\ShippingZoneResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditShippingZone extends EditRecord
{
    protected static string $resource = ShippingZoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalDescription('¿Eliminar esta zona? Las tarifas asociadas también se eliminarán.'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
