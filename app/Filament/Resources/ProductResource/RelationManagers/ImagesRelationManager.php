<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';
    protected static ?string $title = 'Imágenes';

    public function form(Form $form): Form
    {
        return $form->schema([
            FileUpload::make('path')
                ->label('Imagen')
                ->image()
                ->directory('products')
                ->required()
                ->columnSpanFull(),
            TextInput::make('alt_text')
                ->label('Texto alternativo (SEO/accesibilidad)')
                ->maxLength(200)
                ->columnSpanFull(),
            TextInput::make('sort_order')
                ->label('Orden')
                ->numeric()
                ->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('path')
                    ->label('Imagen')
                    ->square(),
                TextColumn::make('alt_text')
                    ->label('Alt text')
                    ->limit(60),
                TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Subir imagen'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalDescription('¿Eliminar esta imagen del producto?'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ]);
    }
}
