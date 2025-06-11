<?php

namespace App\Filament\Resources;

use Filament\Forms\Components\TextInput;
use App\Filament\Resources\ClienteResource\Pages;
use App\Filament\Resources\ClienteResource\RelationManagers;
use App\Models\Cliente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClienteResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            TextInput::make('nombre')
                ->label('Nombre')
                ->required(),

            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required(),

            TextInput::make('telefono')
                ->label('Teléfono')
                ->tel()
                ->nullable(),
        ]);
}

    public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('nombre')
                ->label('Nombre')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('email')
                ->label('Email')
                ->sortable(),

            Tables\Columns\TextColumn::make('telefono')
                ->label('Teléfono')
                ->sortable(),

            Tables\Columns\TextColumn::make('pedidos_count')
                ->label('Pedidos')
                ->counts('pedidos')
                ->sortable(),

            Tables\Columns\TextColumn::make('total_gastado')
                ->label('Total gastado')
                ->money('ARS')
                ->sortable()
                ->state(function ($record) {
                    return $record->pedidos->sum('total');
                }),
        ])
        ->filters([])
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
            'index' => Pages\ListClientes::route('/'),
            'create' => Pages\CreateCliente::route('/create'),
            'edit' => Pages\EditCliente::route('/{record}/edit'),
        ];
    }
}
