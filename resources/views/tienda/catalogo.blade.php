@extends('layouts.tienda')

@section('title', 'Destiny Store - Catálogo')

@section('content')

<section class="bg-dark text-white py-5">
    <div class="container py-4">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h1 class="display-5 fw-bold mb-3">
                    Comprá simple, rápido y seguro
                </h1>

                <p class="lead text-white-50 mb-4">
                    Explorá nuestro catálogo, agregá productos al carrito y generá tu pedido en segundos.
                </p>

                <a href="#productos" class="btn btn-warning btn-lg fw-semibold">
                    Ver productos
                </a>
            </div>

            <div class="col-md-5 mt-4 mt-md-0">
                <div class="card bg-warning border-0 shadow-lg">
                    <div class="card-body p-4 text-dark">
                        <h3 class="fw-bold">Destiny Store</h3>
                        <p class="mb-0">
                            Proyecto e-commerce con carrito, checkout, stock automático,
                            comprobante PDF y panel administrativo.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container py-5" id="productos">

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
            <h2 class="fw-bold mb-1">Catálogo de productos</h2>
            <p class="text-muted mb-0">Productos disponibles para comprar.</p>
        </div>

        <a href="{{ route('carrito.ver') }}" class="btn btn-outline-dark">
            Ver carrito
        </a>
    </div>

    <div class="row g-4">
        @forelse($productos as $producto)
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">

                    @if($producto->imagen)
                        <img
                            src="{{ asset('storage/' . $producto->imagen) }}"
                            class="card-img-top"
                            style="height: 240px; object-fit: cover;"
                            alt="{{ $producto->nombre }}"
                        >
                    @else
                        <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 240px;">
                            Sin imagen
                        </div>
                    @endif

                    <div class="card-body d-flex flex-column p-4">
                        <div class="mb-2">
                            <span class="badge bg-success">
                                Stock: {{ $producto->stock }}
                            </span>
                        </div>

                        <h5 class="card-title fw-bold">
                            {{ $producto->nombre }}
                        </h5>

                        <p class="card-text text-muted">
                            {{ $producto->descripcion ?? 'Sin descripción disponible.' }}
                        </p>

                        <p class="fw-bold fs-4 mb-3 text-dark">
                            ${{ number_format($producto->precio, 2, ',', '.') }}
                        </p>

                        <form action="{{ route('carrito.agregar', $producto) }}" method="POST" class="mt-auto">
                            @csrf

                            <div class="mb-3">

    <label
        for="cantidad-{{ $producto->id }}"
        class="form-label text-muted"
    >
        Cantidad
    </label>

    <input
        id="cantidad-{{ $producto->id }}"
        type="number"
        name="cantidad"
        value="1"
        min="1"
        max="{{ $producto->stock }}"
        class="form-control"
    >

</div>

                            <button type="submit" class="btn btn-dark w-100">
                                Agregar al carrito
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning text-center shadow-sm">
                    No hay productos disponibles por el momento.
                </div>
            </div>
        @endforelse
    </div>
</div>

@endsection