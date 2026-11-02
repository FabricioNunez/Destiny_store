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

use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\Storage;

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
    ->image()
    ->imagePreviewHeight('150')
    ->visibility('public')
    ->nullable()
    ->saveUploadedFileUsing(function ($file): string {
        $cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('services.cloudinary.cloud_name'),
                'api_key' => config('services.cloudinary.api_key'),
                'api_secret' => config('services.cloudinary.api_secret'),
            ],
            'url' => [
                'secure' => true,
            ],
        ]);

        $resultado = $cloudinary->uploadApi()->upload(
            $file->getRealPath(),
            [
                'folder' => 'destiny-store/productos',
                'resource_type' => 'image',
            ]
        );

        return $resultado['secure_url'];
    })
    ->getUploadedFileUsing(function (string $file): ?array {
        if (!filter_var($file, FILTER_VALIDATE_URL)) {
            return null;
        }

        return [
            'name' => basename(parse_url($file, PHP_URL_PATH)),
            'size' => 0,
            'type' => 'image/jpeg',
            'url' => $file,
        ];
        }),
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
