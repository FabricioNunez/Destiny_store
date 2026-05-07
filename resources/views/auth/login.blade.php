<x-guest-layout>

    <div class="bg-warning text-dark text-center p-4">
        <h1 class="fw-bold mb-1">Iniciar sesión</h1>
        <p class="mb-0">Accedé a tu cuenta de Destiny Store</p>
    </div>

    <div class="card-body p-4 p-md-5 bg-white">

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-4">
                <label for="email" class="form-label fw-semibold">
                    Email
                </label>

                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    class="form-control form-control-lg"
                >

                <x-input-error :messages="$errors->get('email')" class="mt-2 text-danger" />
            </div>

            <div class="mb-4">
                <label for="password" class="form-label fw-semibold">
                    Contraseña
                </label>

                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    class="form-control form-control-lg"
                >

                <x-input-error :messages="$errors->get('password')" class="mt-2 text-danger" />
            </div>

            <div class="form-check mb-4">
                <input
                    id="remember_me"
                    type="checkbox"
                    class="form-check-input"
                    name="remember"
                >

                <label class="form-check-label" for="remember_me">
                    Recordarme
                </label>
            </div>

            @if (Route::has('password.request'))
                <div class="text-end mb-4">
                    <a href="{{ route('password.request') }}" class="text-decoration-none text-muted">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>
            @endif

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-dark btn-lg">
                    Iniciar sesión
                </button>
            </div>

            <div class="text-center">
                <a href="{{ route('register') }}" class="text-decoration-none">
                    ¿No tienes cuenta? Registrarse
                </a>
            </div>
        </form>

    </div>

</x-guest-layout>