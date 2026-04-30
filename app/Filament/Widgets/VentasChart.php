<?php

namespace App\Filament\Widgets;

use App\Models\Pedido;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class VentasChart extends ChartWidget
{
    protected static ?string $heading = 'Ventas de los últimos 7 días';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $dias = collect();

        for ($i = 6; $i >= 0; $i--) {
            $fecha = Carbon::today()->subDays($i);

            $total = Pedido::whereDate('fecha', $fecha)
                ->where('estado', '!=', 'cancelado')
                ->sum('total');

            $dias->push([
                'label' => $fecha->format('d/m'),
                'total' => $total,
            ]);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ventas ARS',
                    'data' => $dias->pluck('total')->toArray(),
                ],
            ],
            'labels' => $dias->pluck('label')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}