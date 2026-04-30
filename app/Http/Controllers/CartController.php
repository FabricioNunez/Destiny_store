<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function catalogo()
    {
        $productos = Producto::where('stock', '>', 0)
            ->orderBy('nombre')
            ->get();

        return view('tienda.catalogo', compact('productos'));
    }

    public function carrito()
    {
        $carrito = session()->get('carrito', []);

        return view('tienda.carrito', compact('carrito'));
    }

    public function agregar(Request $request, Producto $producto)
    {
        $cantidad = (int) $request->input('cantidad', 1);

        if ($cantidad < 1) {
            $cantidad = 1;
        }

        if ($cantidad > $producto->stock) {
            return redirect()
                ->back()
                ->with('error', 'No hay suficiente stock disponible.');
        }

        $carrito = session()->get('carrito', []);

        if (isset($carrito[$producto->id])) {
            $nuevaCantidad = $carrito[$producto->id]['cantidad'] + $cantidad;

            if ($nuevaCantidad > $producto->stock) {
                return redirect()
                    ->back()
                    ->with('error', 'No puedes agregar más unidades que el stock disponible.');
            }

            $carrito[$producto->id]['cantidad'] = $nuevaCantidad;
        } else {
            $carrito[$producto->id] = [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'precio' => $producto->precio,
                'imagen' => $producto->imagen,
                'stock' => $producto->stock,
                'cantidad' => $cantidad,
            ];
        }

        session()->put('carrito', $carrito);

        return redirect()
            ->route('carrito.ver')
            ->with('success', 'Producto agregado al carrito.');
    }

    public function actualizar(Request $request, Producto $producto)
    {
    $cantidad = (int) $request->input('cantidad', 1);

    if ($cantidad < 1) {
        $cantidad = 1;
    }

    if ($cantidad > $producto->stock) {
        $cantidad = $producto->stock;
    }

    $carrito = session()->get('carrito', []);

    if (isset($carrito[$producto->id])) {
        $carrito[$producto->id]['cantidad'] = $cantidad;
        session()->put('carrito', $carrito);
    }

    $total = collect($carrito)->sum(function ($item) {
        return $item['precio'] * $item['cantidad'];
    });

    if ($request->expectsJson()) {
        return response()->json([
            'success' => true,
            'cantidad' => $cantidad,
            'subtotal' => $producto->precio * $cantidad,
            'total' => $total,
        ]);
    }

    return redirect()
        ->route('carrito.ver')
        ->with('success', 'Carrito actualizado.');
}

    public function eliminar(Producto $producto)
    {
        $carrito = session()->get('carrito', []);

        if (isset($carrito[$producto->id])) {
            unset($carrito[$producto->id]);
            session()->put('carrito', $carrito);
        }

        return redirect()
            ->route('carrito.ver')
            ->with('success', 'Producto eliminado del carrito.');
    }

    public function vaciar()
    {
        session()->forget('carrito');

        return redirect()
            ->route('carrito.ver')
            ->with('success', 'Carrito vaciado.');
    }

    public function checkout()
{
    $carrito = session()->get('carrito', []);

    if (count($carrito) === 0) {
        return redirect()
            ->route('tienda.catalogo')
            ->with('error', 'Tu carrito está vacío.');
    }

    $total = collect($carrito)->sum(function ($item) {
        return $item['precio'] * $item['cantidad'];
    });

    return view('tienda.checkout', compact('carrito', 'total'));
}

public function procesarCheckout(Request $request)
{
    $request->validate([
        'nombre' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'telefono' => 'nullable|string|max:50',
    ]);

    $carrito = session()->get('carrito', []);

    if (count($carrito) === 0) {
        return redirect()
            ->route('tienda.catalogo')
            ->with('error', 'Tu carrito está vacío.');
    }

    try {
        DB::transaction(function () use ($request, $carrito) {
            $cliente = Cliente::firstOrCreate(
                ['email' => $request->email],
                [
                    'nombre' => $request->nombre,
                    'telefono' => $request->telefono,
                ]
            );

            $total = 0;

            foreach ($carrito as $item) {
                $producto = Producto::findOrFail($item['id']);

                if ($item['cantidad'] > $producto->stock) {
                    throw new \Exception('No hay stock suficiente para ' . $producto->nombre);
                }

                $total += $producto->precio * $item['cantidad'];
            }

            $pedido = Pedido::create([
                'cliente_id' => $cliente->id,
                'fecha' => now(),
                'estado' => 'pendiente',
                'total' => $total,
            ]);

            foreach ($carrito as $item) {
                $producto = Producto::findOrFail($item['id']);

                $pedido->productos()->attach($producto->id, [
                    'cantidad' => $item['cantidad'],
                ]);

                $producto->stock -= $item['cantidad'];
                $producto->save();
            }

            session()->forget('carrito');
            session()->put('ultimo_pedido_id', $pedido->id);
        });

        return redirect()
            ->route('checkout.confirmacion')
            ->with('success', 'Pedido creado correctamente.');
    } catch (\Exception $e) {
        return redirect()
            ->route('checkout')
            ->with('error', $e->getMessage());
    }
}

public function confirmacion()
{
    $pedidoId = session()->get('ultimo_pedido_id');

    if (!$pedidoId) {
        return redirect()->route('tienda.catalogo');
    }

    $pedido = Pedido::with(['cliente', 'productos'])->findOrFail($pedidoId);

    return view('tienda.confirmacion', compact('pedido'));
}
}