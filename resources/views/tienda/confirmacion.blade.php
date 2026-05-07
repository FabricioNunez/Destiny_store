@extends('layouts.tienda')

@section('title', 'Pedido confirmado - Destiny Store')

@section('content')

<div class="container py-5">

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="bg-success text-white text-center p-5">
            <h1 class="fw-bold mb-2">Pedido confirmado</h1>
            <p class="lead mb-0">
                Tu pedido fue registrado correctamente.
            </p>
        </div>

        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <p class="fs-5 mb-1">
                    Gracias por tu compra, <strong>{{ $pedido->cliente->nombre }}</strong>.
                </p>

                <p class="text-muted mb-3">
                    Número de pedido
                </p>

                <h2 class="fw-bold">
                    #{{ $pedido->id }}
                </h2>

                <span class="badge bg-warning text-dark fs-6">
                    {{ ucfirst($pedido->estado) }}
                </span>
            </div>

            <hr>

            <div class="row g-4">
                <div class="col-md-5">
                    <h4 class="fw-bold mb-3">Datos del cliente</h4>

                    <div class="bg-light rounded-4 p-4">
                        <p class="mb-2">
                            <strong>Nombre:</strong><br>
                            {{ $pedido->cliente->nombre }}
                        </p>

                        <p class="mb-2">
                            <strong>Email:</strong><br>
                            {{ $pedido->cliente->email }}
                        </p>

                        <p class="mb-0">
                            <strong>Teléfono:</strong><br>
                            {{ $pedido->cliente->telefono ?? 'No informado' }}
                        </p>

                        <p class="mb-0">
                        <strong>Dirección:</strong><br>
                        {{ $pedido->cliente->direccion }}
</p>
                    </div>
                </div>

                <div class="col-md-7">
                    <h4 class="fw-bold mb-3">Resumen del pedido</h4>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($pedido->productos as $producto)
                                    <tr>
                                        <td>{{ $producto->nombre }}</td>
                                        <td>{{ $producto->pivot->cantidad }}</td>
                                        <td>
                                            ${{ number_format($producto->precio * $producto->pivot->cantidad, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="text-end fs-4 fw-bold">
                        Total: ${{ number_format($pedido->total, 2, ',', '.') }}
                    </div>
                </div>
            </div>

            <div class="d-flex flex-column flex-md-row justify-content-center gap-3 mt-5">
                <a href="{{ route('pedidos.pdf', $pedido) }}" class="btn btn-success btn-lg">
                    Descargar comprobante PDF
                </a>

                <a href="{{ route('tienda.catalogo') }}" class="btn btn-outline-dark btn-lg">
                    Volver a la tienda
                </a>
            </div>
        </div>
    </div>

</div>

@endsection