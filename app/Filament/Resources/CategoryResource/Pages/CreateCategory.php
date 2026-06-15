<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Models\Category;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $parentId = $data['parent_id'] ?? null;

        if ($parentId === null) {
            $data['depth'] = 0;
            $data['path']  = '0'; // updated after insert in seeder style; we handle it here
        } else {
            $parent = Category::find($parentId);
            $data['depth'] = ($parent->depth ?? 0) + 1;
            $data['path']  = '0'; // placeholder; AfterCreate handles final path
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        $parentId = $record->parent_id;

        if ($parentId === null) {
            $record->update(['path' => (string) $record->id]);
        } else {
            $parent = Category::find($parentId);
            $record->update(['path' => $parent->path . '/' . $record->id]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
