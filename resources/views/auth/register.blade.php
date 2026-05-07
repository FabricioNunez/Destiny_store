<x-guest-layout>

    <div class="bg-warning text-dark text-center p-4">
        <h1 class="fw-bold mb-1">Crear cuenta</h1>
        <p class="mb-0">Registrate en Destiny Store</p>
    </div>

    <div class="card-body p-4 p-md-5 bg-white">

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="mb-4">
                <label for="name" class="form-label fw-semibold">Nombre</label>

                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    autocomplete="name"
                    class="form-control form-control-lg"
                >

                <x-input-error :messages="$errors->get('name')" class="mt-2 text-danger" />
            </div>

            <div class="mb-4">
                <label for="email" class="form-label fw-semibold">Email</label>

                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="username"
                    class="form-control form-control-lg"
                >

                <x-input-error :messages="$errors->get('email')" class="mt-2 text-danger" />
            </div>

            <div class="mb-4">
                <label for="password" class="form-label fw-semibold">Contraseña</label>

                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    class="form-control form-control-lg"
                >

                <x-input-error :messages="$errors->get('password')" class="mt-2 text-danger" />
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label fw-semibold">Confirmar contraseña</label>

                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    class="form-control form-control-lg"
                >

                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-danger" />
            </div>

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-dark btn-lg">
                    Crear cuenta
                </button>
            </div>

            <div class="text-center">
                <a href="{{ route('login') }}" class="text-decoration-none">
                    ¿Ya tienes cuenta? Iniciar sesión
                </a>
            </div>
        </form>

    </div>

</x-guest-layout>