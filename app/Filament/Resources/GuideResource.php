<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuideResource\Pages;
use App\Models\Guide;
use App\Models\Product;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class GuideResource extends Resource
{
    protected static ?string $model = Guide::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Contenido';
    protected static ?int $navigationSort = 10;
    protected static ?string $modelLabel = 'Mini-guía';
    protected static ?string $pluralModelLabel = 'Mini-guías';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                Grid::make(2)->schema([
                    TextInput::make('title')
                        ->label('Título')
                        ->required()
                        ->maxLength(200)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                    TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->unique(Guide::class, 'slug', ignoreRecord: true),
                ]),
                Select::make('age_stage_id')
                    ->label('Etapa del bebé')
                    ->relationship('ageStage', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Textarea::make('excerpt')
                    ->label('Resumen corto')
                    ->required()
                    ->rows(2)
                    ->maxLength(300)
                    ->columnSpanFull(),
                MarkdownEditor::make('body')
                    ->label('Contenido')
                    ->required()
                    ->columnSpanFull(),
                FileUpload::make('cover_image')
                    ->label('Imagen de portada')
                    ->image()
                    ->directory('guides')
                    ->columnSpanFull(),
                Select::make('products')
                    ->label('Productos recomendados')
                    ->relationship('products', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
                Grid::make(2)->schema([
                    Select::make('status')
                        ->label('Estado')
                        ->options(['draft' => 'Borrador', 'published' => 'Publicada'])
                        ->default('draft')
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set, string $state) {
                            if ($state === 'published') {
                                $set('published_at', now()->toDateTimeString());
                            }
                        }),
                    \Filament\Forms\Components\DateTimePicker::make('published_at')
                        ->label('Publicada en')
                        ->nullable()
                        ->visible(fn(Get $get) => $get('status') === 'published'),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->limit(50)
                    ->sortable(),
                TextColumn::make('ageStage.name')
                    ->label('Etapa')
                    ->badge()
                    ->color('info'),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => $state === 'published' ? 'Publicada' : 'Borrador')
                    ->color(fn(string $state) => $state === 'published' ? 'success' : 'gray'),
                TextColumn::make('published_at')
                    ->label('Publicada')
                    ->date('d/m/Y')
                    ->placeholder('No publicada')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Creada')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('age_stage_id')
                    ->label('Etapa')
                    ->relationship('ageStage', 'name'),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(['draft' => 'Borrador', 'published' => 'Publicada']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->requiresConfirmation(),
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
            'index'  => Pages\ListGuides::route('/'),
            'create' => Pages\CreateGuide::route('/create'),
            'edit'   => Pages\EditGuide::route('/{record}/edit'),
        ];
    }
}
