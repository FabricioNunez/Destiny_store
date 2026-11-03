
<x-guest-layout>
    <div style="width: 100%; max-width: 420px; margin: 0 auto;">

        <h4 style="text-align: center; color: #222; font-weight: 700; margin-bottom: 12px;">
            Recuperar contraseña
        </h4>

        <p style="color: #666; font-size: 14px; text-align: center; margin-bottom: 25px;">
            Ingresá tu correo electrónico y te enviaremos un enlace
            para restablecer tu contraseña.
        </p>

        @if (session('status'))
            <div style="background: #d1e7dd; color: #0f5132; padding: 12px; border-radius: 8px; margin-bottom: 15px; font-size: 14px;">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div style="margin-bottom: 20px;">
                <label for="email" style="display: block; font-weight: 600; color: #333; margin-bottom: 8px;">
                    Correo electrónico
                </label>

                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="ejemplo@correo.com"
                    style="display: block; width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid #ccc; border-radius: 8px; background: #fff; color: #222; font-size: 14px;"
                >

                @error('email')
                    <p style="color: #dc3545; font-size: 13px; margin-top: 6px;">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <button
                type="submit"
                style="display: block; width: 100%; padding: 12px; background: #ffc107; color: #171717; border: none; border-radius: 8px; font-weight: 700; font-size: 15px; cursor: pointer;"
            >
                Enviar enlace de recuperación
            </button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <a href="{{ route('login') }}" style="color: #666; font-size: 14px; text-decoration: none;">
                Volver al inicio de sesión
            </a>
        </div>

    </div>
</x-guest-layout>
