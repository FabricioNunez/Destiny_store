<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Barryvdh\DomPDF\Facade\Pdf;

class PedidoPdfController extends Controller
{
    public function descargar(Pedido $pedido)
    {
        $pedido->load(['cliente', 'productos']);

        $pdf = Pdf::loadView('pdf.pedido', compact('pedido'));

        return $pdf->download('pedido-' . $pedido->id . '.pdf');
    }
}