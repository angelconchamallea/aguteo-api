<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\AgeStage;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Catálogo';
    protected static ?int $navigationSort = 10;
    protected static ?string $modelLabel = 'Producto';
    protected static ?string $pluralModelLabel = 'Productos';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('Tabs')->tabs([

                Tabs\Tab::make('General')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('sku')
                            ->label('SKU')
                            ->required()
                            ->maxLength(80)
                            ->unique(Product::class, 'sku', ignoreRecord: true),
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(200)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                    ]),
                    TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->unique(Product::class, 'slug', ignoreRecord: true)
                        ->columnSpanFull(),
                    Grid::make(2)->schema([
                        Select::make('brand_id')
                            ->label('Marca')
                            ->relationship('brand', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('category_id')
                            ->label('Categoría')
                            ->options(
                                Category::with('parent')
                                    ->orderBy('depth')
                                    ->orderBy('sort_order')
                                    ->get()
                                    ->mapWithKeys(fn(Category $c) => [
                                        $c->id => ($c->parent ? $c->parent->name . ' → ' : '') . $c->name,
                                    ])
                            )
                            ->searchable()
                            ->nullable(),
                    ]),
                    Textarea::make('short_description')
                        ->label('Descripción corta')
                        ->rows(2)
                        ->maxLength(300)
                        ->columnSpanFull(),
                    MarkdownEditor::make('description')
                        ->label('Descripción completa')
                        ->columnSpanFull(),
                ]),

                Tabs\Tab::make('Precios y stock')->schema([
                    Grid::make(3)->schema([
                        TextInput::make('price')
                            ->label('Precio ($)')
                            ->numeric()
                            ->required()
                            ->prefix('$')
                            ->minValue(0),
                        TextInput::make('compare_at_price')
                            ->label('Precio anterior ($)')
                            ->numeric()
                            ->prefix('$')
                            ->nullable()
                            ->helperText('Dejar vacío si no hay descuento de precio tachado'),
                        TextInput::make('cost_price')
                            ->label('Costo ($)')
                            ->numeric()
                            ->prefix('$')
                            ->nullable()
                            ->helperText('Solo visible para ti — nunca se muestra en la tienda'),
                    ]),
                    Toggle::make('has_variants')
                        ->label('Tiene variantes (tallas/colores)')
                        ->live()
                        ->afterStateUpdated(function (Set $set, bool $state) {
                            if ($state) {
                                $set('stock', null);
                            }
                        }),
                    Grid::make(2)->schema([
                        TextInput::make('stock')
                            ->label('Stock')
                            ->numeric()
                            ->minValue(0)
                            ->nullable()
                            ->visible(fn(Get $get) => ! $get('has_variants'))
                            ->helperText('El stock se maneja aquí cuando no hay variantes'),
                        TextInput::make('low_stock_threshold')
                            ->label('Alerta de stock bajo')
                            ->numeric()
                            ->default(5)
                            ->minValue(0)
                            ->helperText('Se mostrará alerta en el admin cuando el stock baje de este número'),
                    ])->visible(fn(Get $get) => ! $get('has_variants')),
                    \Filament\Forms\Components\Placeholder::make('variants_notice')
                        ->label('')
                        ->content('El stock ahora se maneja por talla en la pestaña Variantes.')
                        ->visible(fn(Get $get) => $get('has_variants')),
                    TextInput::make('weight_grams')
                        ->label('Peso (gramos)')
                        ->numeric()
                        ->nullable(),
                ]),

                Tabs\Tab::make('Clasificación')->schema([
                    Select::make('ageStages')
                        ->label('Etapas')
                        ->relationship('ageStages', 'name')
                        ->multiple()
                        ->preload()
                        ->searchable(),
                    Select::make('tags')
                        ->label('Etiquetas')
                        ->relationship('tags', 'name')
                        ->multiple()
                        ->searchable()
                        ->createOptionForm([
                            TextInput::make('name')->required()->maxLength(50)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                            TextInput::make('slug')->required(),
                        ]),
                    Grid::make(2)->schema([
                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'draft'         => 'Borrador',
                                'active'        => 'Activo',
                                'out_of_stock'  => 'Agotado',
                            ])
                            ->default('draft')
                            ->required(),
                        Toggle::make('featured')
                            ->label('Destacado')
                            ->helperText('Se muestra en la página principal'),
                    ]),
                ]),

            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')
                    ->label('Producto')
                    ->searchable()
                    ->limit(40)
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->badge()
                    ->sortable(),
                TextColumn::make('brand.name')
                    ->label('Marca')
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Precio')
                    ->formatStateUsing(fn(?int $state) => '$' . number_format($state ?? 0, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('total_stock')
                    ->label('Stock')
                    ->getStateUsing(fn(Product $record) => $record->total_stock)
                    ->badge()
                    ->color(function (Product $record): string {
                        $stock = $record->total_stock;
                        $threshold = $record->low_stock_threshold ?? 5;
                        if ($stock === 0) return 'danger';
                        if ($stock <= $threshold) return 'warning';
                        return 'success';
                    }),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'active'        => 'Activo',
                        'draft'         => 'Borrador',
                        'out_of_stock'  => 'Agotado',
                        default         => $state,
                    })
                    ->color(fn(string $state) => match ($state) {
                        'active'       => 'success',
                        'draft'        => 'gray',
                        'out_of_stock' => 'danger',
                        default        => 'gray',
                    }),
                ToggleColumn::make('featured')
                    ->label('Destacado'),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('brand_id')
                    ->label('Marca')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'draft'        => 'Borrador',
                        'active'       => 'Activo',
                        'out_of_stock' => 'Agotado',
                    ]),
                Tables\Filters\Filter::make('low_stock')
                    ->label('Stock bajo')
                    ->query(fn(Builder $query) => $query->whereRaw('stock <= low_stock_threshold')->where('has_variants', false)),
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalDescription('El producto pasará a la papelera y dejará de mostrarse en la tienda.'),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('change_status')
                        ->label('Cambiar estado')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Select::make('status')
                                ->label('Nuevo estado')
                                ->options([
                                    'draft'        => 'Borrador',
                                    'active'       => 'Activo',
                                    'out_of_stock' => 'Agotado',
                                ])
                                ->required(),
                        ])
                        ->action(fn($records, array $data) => $records->each->update(['status' => $data['status']]))
                        ->requiresConfirmation(),
                    Tables\Actions\DeleteBulkAction::make()->requiresConfirmation(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\VariantsRelationManager::class,
            RelationManagers\ImagesRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
