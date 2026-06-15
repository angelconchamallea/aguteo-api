<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;
    protected static ?string $navigationIcon = 'heroicon-o-folder-open';
    protected static ?string $navigationGroup = 'Catálogo';
    protected static ?int $navigationSort = 30;
    protected static ?string $modelLabel = 'Categoría';
    protected static ?string $pluralModelLabel = 'Categorías';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                \Filament\Forms\Components\Select::make('parent_id')
                    ->label('Sección padre (dejar vacío para crear una sección raíz)')
                    ->options(Category::whereNull('parent_id')->orderBy('sort_order')->pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->label('Nombre')
                        ->required()
                        ->maxLength(80)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                    TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->unique(Category::class, 'slug', ignoreRecord: true),
                ]),
                Textarea::make('description')
                    ->label('Descripción')
                    ->rows(2)
                    ->columnSpanFull(),
                Grid::make(3)->schema([
                    ColorPicker::make('color_token')
                        ->label('Color')
                        ->nullable(),
                    TextInput::make('icon')
                        ->label('Ícono (emoji o nombre)')
                        ->maxLength(50),
                    TextInput::make('sort_order')
                        ->label('Orden')
                        ->numeric()
                        ->default(0),
                ]),
                Toggle::make('is_active')
                    ->label('Activa')
                    ->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('depth')
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn(Category $record) => str_repeat('— ', $record->depth) . $record->name),
                TextColumn::make('parent.name')
                    ->label('Sección padre')
                    ->default('Raíz')
                    ->sortable(),
                TextColumn::make('color_token')
                    ->label('Color')
                    ->html()
                    ->formatStateUsing(fn(?string $state) => $state
                        ? "<span style='display:inline-flex;align-items:center;gap:6px;'><span style='width:16px;height:16px;border-radius:3px;background:{$state};border:1px solid rgba(0,0,0,.15);flex-shrink:0;'></span>{$state}</span>"
                        : '<span style="color:#9ca3af">—</span>'),
                TextColumn::make('depth')
                    ->label('Nivel')
                    ->badge()
                    ->formatStateUsing(fn(int $state) => $state === 0 ? 'Sección' : 'Subcategoría')
                    ->color(fn(int $state) => $state === 0 ? 'success' : 'gray'),
                TextColumn::make('products_count')
                    ->label('Productos')
                    ->counts('products'),
                IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('depth')
                    ->label('Tipo')
                    ->options([0 => 'Secciones (raíz)', 1 => 'Subcategorías']),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activa'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalDescription('¿Seguro? Los productos y subcategorías asociados quedarán sin categoría.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit'   => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
