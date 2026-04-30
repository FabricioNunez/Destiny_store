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
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-3">Datos del cliente</h4>

                    <form action="{{ route('checkout.procesar') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Nombre completo</label>
                            <input
                                type="text"
                                name="nombre"
                                value="{{ old('nombre') }}"
                                class="form-control form-control-lg @error('nombre') is-invalid @enderror"
                                placeholder="Ej: Fabricio Nuñez"
                                required
                            >

                            @error('nombre')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input
                                type="email"
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

                        <div class="mb-4">
                            <label class="form-label">Teléfono</label>
                            <input
                                type="text"
                                name="telefono"
                                value="{{ old('telefono') }}"
                                class="form-control form-control-lg @error('telefono') is-invalid @enderror"
                                placeholder="Opcional"
                            >

                            @error('telefono')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="alert alert-light border">
                            <strong>Compra simulada:</strong>
                            este proyecto no procesa pagos reales. El pedido queda registrado como pendiente.
                        </div>

                        <button type="submit" class="btn btn-success btn-lg w-100">
                            Confirmar pedido
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-3">Resumen del pedido</h4>

                    @foreach($carrito as $item)
                        <div class="d-flex gap-3 border-bottom py-3">
                            @if($item['imagen'])
                                <img
                                    src="{{ asset('storage/' . $item['imagen']) }}"
                                    style="width: 65px; height: 65px; object-fit: cover;"
                                    class="rounded-3"
                                    alt="{{ $item['nombre'] }}"
                                >
                            @else
                                <div class="bg-secondary text-white rounded-3 d-flex align-items-center justify-content-center" style="width: 65px; height: 65px;">
                                    Sin img
                                </div>
                            @endif

                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between gap-2">
                                    <strong>{{ $item['nombre'] }}</strong>
                                    <span>
                                        ${{ number_format($item['precio'] * $item['cantidad'], 2, ',', '.') }}
                                    </span>
                                </div>

                                <small class="text-muted">
                                    Cantidad: {{ $item['cantidad'] }}
                                </small>
                            </div>
                        </div>
                    @endforeach

                    <div class="d-flex justify-content-between mt-4 fs-4">
                        <strong>Total:</strong>
                        <strong>
                            ${{ number_format($total, 2, ',', '.') }}
                        </strong>
                    </div>

                    <a href="{{ route('carrito.ver') }}" class="btn btn-outline-dark w-100 mt-4">
                        Volver al carrito
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection