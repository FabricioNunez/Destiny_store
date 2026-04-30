<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Destiny Store')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('tienda.catalogo') }}">
            Destiny Store
        </a>

        <div class="d-flex gap-2">
            <a href="{{ route('tienda.catalogo') }}" class="btn btn-outline-light btn-sm">
                Tienda
            </a>

            <a href="{{ route('carrito.ver') }}" class="btn btn-warning btn-sm">
                Carrito
                @php
                    $cantidadCarrito = collect(session('carrito', []))->sum('cantidad');
                @endphp

                @if($cantidadCarrito > 0)
                    <span class="badge bg-dark ms-1">{{ $cantidadCarrito }}</span>
                @endif
            </a>
        </div>
    </div>
</nav>

<main>
    @yield('content')
</main>

<footer class="bg-dark text-white mt-5 py-4">
    <div class="container text-center">
        <h5 class="fw-bold mb-1">Destiny Store</h5>
        <p class="text-white-50 mb-0">
            Tienda online desarrollada con Laravel y FilamentPHP.
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@yield('scripts')

</body>
</html>