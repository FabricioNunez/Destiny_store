@extends('layouts.tienda')

@section('title', 'Carrito - Destiny Store')

@section('content')

<div class="container py-5">

    @if(session('success'))
        <div class="alert alert-success shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h1 class="fw-bold mb-1">Carrito de compras</h1>
            <p class="text-muted mb-0">Revisá tus productos antes de finalizar la compra.</p>
        </div>

        <a href="{{ route('tienda.catalogo') }}" class="btn btn-outline-dark">
            Seguir comprando
        </a>
    </div>

    @php
        $total = 0;
    @endphp

    @if(count($carrito) > 0)
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>Cantidad</th>
                                <th>Subtotal</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($carrito as $item)
                                @php
                                    $subtotal = $item['precio'] * $item['cantidad'];
                                    $total += $subtotal;
                                @endphp

                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            @if($item['imagen'])
                                                <img
                                                    src="{{ asset('storage/' . $item['imagen']) }}"
                                                    style="width: 75px; height: 75px; object-fit: cover;"
                                                    class="rounded-3"
                                                    alt="{{ $item['nombre'] }}"
                                                >
                                            @else
                                                <div class="bg-secondary text-white rounded-3 d-flex align-items-center justify-content-center" style="width: 75px; height: 75px;">
                                                    Sin img
                                                </div>
                                            @endif

                                            <div>
                                                <strong>{{ $item['nombre'] }}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    Stock disponible: {{ $item['stock'] }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        ${{ number_format($item['precio'], 2, ',', '.') }}
                                    </td>

                                    <td style="width: 150px;">
                                        <form action="{{ route('carrito.actualizar', $item['id']) }}" method="POST" class="form-actualizar-carrito">
                                            @csrf
                                            @method('PATCH')

                                            <input
                                                type="number"
                                                name="cantidad"
                                                value="{{ $item['cantidad'] }}"
                                                min="1"
                                                max="{{ $item['stock'] }}"
                                                data-precio="{{ $item['precio'] }}"
                                                data-producto-id="{{ $item['id'] }}"
                                                class="form-control cantidad-input"
                                            >
                                        </form>
                                    </td>

                                    <td>
                                        <span class="subtotal-item fw-semibold" data-producto-id="{{ $item['id'] }}">
                                            ${{ number_format($subtotal, 2, ',', '.') }}
                                        </span>
                                    </td>

                                    <td class="text-end">
                                        <form action="{{ route('carrito.eliminar', $item['id']) }}" method="POST">
                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-sm btn-outline-danger">
                                                Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end fs-5">Total:</th>
                                <th colspan="2" class="fs-4">
                                    <span id="total-carrito">
                                        ${{ number_format($total, 2, ',', '.') }}
                                    </span>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <form action="{{ route('carrito.vaciar') }}" method="POST">
                        @csrf
                        @method('DELETE')

                        <button class="btn btn-outline-danger">
                            Vaciar carrito
                        </button>
                    </form>

                    <a href="{{ route('checkout') }}" class="btn btn-success btn-lg">
                        Continuar compra
                    </a>
                </div>

            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center p-5">
                <h3 class="fw-bold mb-2">Tu carrito está vacío</h3>
                <p class="text-muted">Agregá productos desde el catálogo para continuar.</p>

                <a href="{{ route('tienda.catalogo') }}" class="btn btn-dark">
                    Ver productos
                </a>
            </div>
        </div>
    @endif

</div>

@endsection

@section('scripts')
<script>
    function formatearPrecio(valor) {
        return new Intl.NumberFormat('es-AR', {
            style: 'currency',
            currency: 'ARS',
            minimumFractionDigits: 2
        }).format(valor);
    }

    function recalcularTotalVisual() {
        let total = 0;

        document.querySelectorAll('.cantidad-input').forEach(input => {
            const precio = parseFloat(input.dataset.precio);
            const cantidad = parseInt(input.value);

            total += precio * cantidad;
        });

        document.getElementById('total-carrito').textContent = formatearPrecio(total);
    }

    document.querySelectorAll('.cantidad-input').forEach(input => {
        let timer = null;

        input.addEventListener('input', function () {
            const precio = parseFloat(this.dataset.precio);
            const productoId = this.dataset.productoId;
            const max = parseInt(this.max);

            let cantidad = parseInt(this.value);

            if (isNaN(cantidad) || cantidad < 1) {
                cantidad = 1;
                this.value = 1;
            }

            if (cantidad > max) {
                cantidad = max;
                this.value = max;
            }

            const subtotal = precio * cantidad;

            document.querySelector(`.subtotal-item[data-producto-id="${productoId}"]`).textContent = formatearPrecio(subtotal);

            recalcularTotalVisual();

            clearTimeout(timer);

            timer = setTimeout(() => {
                const form = this.closest('form');
                const formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData
                });
            }, 500);
        });
    });
</script>
@endsection