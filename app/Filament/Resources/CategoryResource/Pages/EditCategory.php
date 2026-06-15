<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Models\Category;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalDescription('¿Seguro? Los productos y subcategorías asociados quedarán sin categoría.'),
        ];
    }

    protected function afterSave(): void
    {
        // Recalculate path if parent changed
        $record = $this->record->fresh();
        $parentId = $record->parent_id;

        if ($parentId === null) {
            $record->update(['depth' => 0, 'path' => (string) $record->id]);
        } else {
            $parent = Category::find($parentId);
            $record->update([
                'depth' => ($parent->depth ?? 0) + 1,
                'path'  => $parent->path . '/' . $record->id,
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
