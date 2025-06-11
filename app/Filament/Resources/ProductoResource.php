<?php

namespace App\Filament\Resources;


use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

use App\Filament\Resources\ProductoResource\Pages;
use App\Filament\Resources\ProductoResource\RelationManagers;
use App\Models\Producto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductoResource extends Resource
{
    protected static ?string $model = Producto::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            TextInput::make('nombre')
                ->label('Nombre')
                ->required()
                ->maxLength(100),

            Textarea::make('descripcion')
                ->label('Descripción')
                ->rows(3)
                ->nullable(),

            TextInput::make('precio')
                ->label('Precio')
                ->numeric()
                ->required(),

            TextInput::make('stock')
                ->label('Stock')
                ->numeric()
                ->required(),

            FileUpload::make('imagen')
                ->label('Imagen')
                ->directory('productos')
                ->image()
                ->imagePreviewHeight('150')
                ->visibility('public')
                ->nullable(),
        ]);
}

    public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\ImageColumn::make('imagen')
                ->label('Imagen')
                ->square()
                ->height(50)
                ->circular(),

            Tables\Columns\TextColumn::make('nombre')
                ->label('Nombre')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('precio')
                ->label('Precio')
                ->money('ARS')
                ->sortable()
                ->alignRight(),

            Tables\Columns\TextColumn::make('stock')
                ->label('Existencias')
                ->sortable()
                ->alignCenter(),
        ])
        ->filters([
            //
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
}


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductos::route('/'),
            'create' => Pages\CreateProducto::route('/create'),
            'edit' => Pages\EditProducto::route('/{record}/edit'),
        ];
    }
}
