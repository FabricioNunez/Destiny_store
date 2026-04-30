<?php

namespace App\Filament\Widgets;

use App\Models\Pedido;
use App\Models\Producto;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class StatsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Total vendido', 'ARS ' . number_format(
                Pedido::where('estado', '!=', 'cancelado')->sum('total'),
                2,
                ',',
                '.'
            ))
                ->description('Suma de pedidos')
                ->color('success'),

            Card::make('Pedidos totales', Pedido::count())
                ->color('primary'),

            Card::make('Pedidos pendientes', Pedido::where('estado', 'pendiente')->count())
                ->color('warning'),

            Card::make('Stock bajo', Producto::where('stock', '<=', 3)->count())
                ->description('Productos con poco stock')
                ->color('danger'),
        ];
    }
}