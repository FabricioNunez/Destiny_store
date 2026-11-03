
@extends('layouts.tienda')

@section('title', 'Checkout - Destiny Store')

@section('content')

<div class="container py-5">

    <div class="mb-4">
        <h1 class="fw-bold mb-1">Finalizar compra</h1>
        <p class="text-muted mb-0">
            Completá tus datos para generar el pedido.
        </p>
    </div>

    @if(session('error'))
        <div class="alert alert-danger shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="row g-4">

        {{-- DATOS DEL CLIENTE --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">

                    <h4 class="fw-bold mb-3">Datos del cliente</h4>

                    <form action="{{ route('checkout.procesar') }}" method="POST">
                        @csrf

                        {{-- Nombre --}}
                        <div class="mb-3">
                            <label for="nombre" class="form-label">
                                Nombre completo
                            </label>

                            <input
                                type="text"
                                id="nombre"
                                name="nombre"
                                value="{{ old('nombre') }}"
                                class="form-control form-control-lg @error('nombre') is-invalid @enderror"
                                placeholder="Ej: Juan Pérez"
                                required
                            >

                            @error('nombre')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                Email
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                class="form-control form-control-lg @error('email') is-invalid @enderror"
                                placeholder="ejemplo@email.com"
                                required
                            >

                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Teléfono --}}
                        <div class="mb-3">
                            <label for="telefono" class="form-label">
                                Teléfono
                            </label>

                            <input
                                type="tel"
                                id="telefono"
                                name="telefono"
                                value="{{ old('telefono') }}"
                                class="form-control form-control-lg @error('telefono') is-invalid @enderror"
                                placeholder="Ej: 3704123456"
                                inputmode="numeric"
                                pattern="[0-9]{10}"
                                maxlength="10"
                                oninput="this.value = this.value.replace(/\D/g, '').slice(0, 10)"
                            >

                            @error('telefono')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                            <div class="form-text">
                                Opcional. Ingresá 10 dígitos, sin espacios ni guiones.
                            </div>
                        </div>

                        {{-- Dirección --}}
                        <div class="mb-4">
                            <label for="direccion" class="form-label">
                                Dirección de entrega
                            </label>

                            <textarea
                                id="direccion"
                                name="direccion"
                                class="form-control form-control-lg @error('direccion') is-invalid @enderror"
                                rows="3"
                                placeholder="Ej: Av. Siempre Viva 742, Mendoza"
                                required
                            >{{ old('direccion') }}</textarea>

                            @error('direccion')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Aviso Mercado Pago --}}
                        <div class="alert alert-info small mb-3">
                            <strong>Pago de demostración:</strong>
                            serás redirigido a Mercado Pago para completar
                            una operación de prueba.

                            No se realizan cobros reales. Una vez aprobado
                            el pago, el estado de tu pedido se actualizará
                            automáticamente.
                        </div>

                        {{-- Botones --}}
                        <div class="d-grid gap-2">
                            <button
                                type="submit"
                                class="btn btn-success btn-lg"
                            >
                                Confirmar pedido
                            </button>

                            <a
                                href="{{ route('carrito.ver') }}"
                                class="btn btn-danger btn-lg"
                            >
                                Cancelar compra
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        {{-- RESUMEN DEL PEDIDO --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">

                    <h4 class="fw-bold mb-3">Resumen del pedido</h4>

                    @foreach($carrito as $item)
                        <div class="d-flex gap-3 border-bottom py-3">

                            @if(!empty($item['imagen']))
                                <img
                                    src="{{ filter_var($item['imagen'], FILTER_VALIDATE_URL) ? $item['imagen'] : asset('storage/' . $item['imagen']) }}"
                                    style="width: 65px; height: 65px; object-fit: cover; flex-shrink: 0;"
                                    class="rounded-3"
                                    alt="{{ $item['nombre'] }}"
                                >
                            @else
                                <div
                                    class="bg-secondary text-white rounded-3 d-flex align-items-center justify-content-center text-center"
                                    style="width: 65px; height: 65px; flex-shrink: 0; font-size: 12px;"
                                >
                                    Sin img
                                </div>
                            @endif

                            <div class="flex-grow-1" style="min-width: 0;">
                                <div class="d-flex justify-content-between gap-2">
                                    <strong>{{ $item['nombre'] }}</strong>

                                    <span class="text-nowrap">
                                        ${{ number_format($item['precio'] * $item['cantidad'], 2, ',', '.') }}
                                    </span>
                                </div>

                                <small class="text-muted">
                                    Cantidad: {{ $item['cantidad'] }}
                                </small>
                            </div>

                        </div>
                    @endforeach

                    {{-- Total --}}
                    <div class="d-flex justify-content-between align-items-center mt-4 mb-3 fs-4">
                        <strong>Total:</strong>

                        <strong>
                            ${{ number_format($total, 2, ',', '.') }}
                        </strong>
                    </div>

                    {{-- Volver al carrito --}}
                    <a
                        href="{{ route('carrito.ver') }}"
                        class="btn btn-primary w-100"
                    >
                        Volver al carrito
                    </a>

                </div>
            </div>
        </div>

    </div>
</div>

@endsection
