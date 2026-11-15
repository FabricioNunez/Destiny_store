<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Controlador de sesiones predeterminado
    |--------------------------------------------------------------------------
    |
    | Esta opción determina cómo Laravel almacena las sesiones de los usuarios.
    | Se admiten diferentes métodos de almacenamiento, siendo la base de datos
    | una buena opción para conservar las sesiones entre solicitudes.
    |
    | Opciones disponibles: "file", "cookie", "database", "memcached",
    |                       "redis", "dynamodb", "array"
    |
    */

    'driver' => env('SESSION_DRIVER', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Duración de las sesiones
    |--------------------------------------------------------------------------
    |
    | Define la cantidad de minutos que una sesión puede permanecer inactiva
    | antes de expirar. Si se desea que la sesión termine al cerrar el
    | navegador, puede utilizarse la opción expire_on_close.
    |
    */

    'lifetime' => (int) env('SESSION_LIFETIME', 120),

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    /*
    |--------------------------------------------------------------------------
    | Cifrado de sesiones
    |--------------------------------------------------------------------------
    |
    | Permite cifrar los datos de sesión antes de almacenarlos.
    | Laravel realiza automáticamente el cifrado y descifrado,
    | sin modificar la forma habitual de utilizar las sesiones.
    |
    */

    'encrypt' => env('SESSION_ENCRYPT', false),

    /*
    |--------------------------------------------------------------------------
    | Ubicación de los archivos de sesión
    |--------------------------------------------------------------------------
    |
    | Cuando se utiliza el controlador "file", los datos de sesión
    | se almacenan en archivos dentro del directorio indicado.
    |
    */

    'files' => storage_path('framework/sessions'),

    /*
    |--------------------------------------------------------------------------
    | Conexión de base de datos para sesiones
    |--------------------------------------------------------------------------
    |
    | Permite especificar la conexión que se utilizará para administrar
    | las sesiones cuando se emplean los controladores "database" o "redis".
    | La conexión debe estar definida en la configuración correspondiente.
    |
    */

    'connection' => env('SESSION_CONNECTION'),

    /*
    |--------------------------------------------------------------------------
    | Tabla de sesiones
    |--------------------------------------------------------------------------
    |
    | Cuando se utiliza el controlador "database", esta opción indica
    | en qué tabla se almacenarán las sesiones de los usuarios.
    |
    */

    'table' => env('SESSION_TABLE', 'sessions'),

    /*
    |--------------------------------------------------------------------------
    | Almacén de caché para sesiones
    |--------------------------------------------------------------------------
    |
    | Define el almacén de caché utilizado por los controladores de sesión
    | que dependen de sistemas de caché.
    |
    | Afecta a: "dynamodb", "memcached", "redis"
    |
    */

    'store' => env('SESSION_STORE'),

    /*
    |--------------------------------------------------------------------------
    | Limpieza automática de sesiones
    |--------------------------------------------------------------------------
    |
    | Algunos controladores necesitan eliminar periódicamente las sesiones
    | expiradas. Esta configuración establece la probabilidad de ejecutar
    | esa limpieza en cada solicitud.
    |
    | Por defecto: 2 posibilidades de cada 100 solicitudes.
    |
    */

    'lottery' => [2, 100],

    /*
    |--------------------------------------------------------------------------
    | Nombre de la cookie de sesión
    |--------------------------------------------------------------------------
    |
    | Define el nombre de la cookie que Laravel crea para identificar
    | la sesión del usuario. Normalmente no es necesario modificarlo.
    |
    */

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug(env('APP_NAME', 'laravel'), '_').'_session'
    ),

    /*
    |--------------------------------------------------------------------------
    | Ruta de la cookie de sesión
    |--------------------------------------------------------------------------
    |
    | Determina en qué rutas de la aplicación estará disponible
    | la cookie de sesión. Por defecto se utiliza la raíz del sitio.
    |
    */

    'path' => env('SESSION_PATH', '/'),

    /*
    |--------------------------------------------------------------------------
    | Dominio de la cookie de sesión
    |--------------------------------------------------------------------------
    |
    | Permite especificar el dominio para el que será válida la cookie.
    | Si no se configura, el navegador utiliza el dominio que la generó.
    |
    */

    'domain' => env('SESSION_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Cookies exclusivas para HTTPS
    |--------------------------------------------------------------------------
    |
    | Cuando esta opción está activada, las cookies de sesión solamente
    | se envían mediante conexiones HTTPS, evitando su transmisión
    | a través de conexiones HTTP sin cifrar.
    |
    */

    'secure' => env('SESSION_SECURE_COOKIE'),

    /*
    |--------------------------------------------------------------------------
    | Acceso exclusivo mediante HTTP
    |--------------------------------------------------------------------------
    |
    | Impide que JavaScript acceda directamente a la cookie de sesión.
    | Esta protección se encuentra activada por defecto.
    |
    */

    'http_only' => env('SESSION_HTTP_ONLY', true),

    /*
    |--------------------------------------------------------------------------
    | Política SameSite de las cookies
    |--------------------------------------------------------------------------
    |
    | Controla cómo se envían las cookies en solicitudes entre sitios.
    | Ayuda a reducir determinados ataques CSRF.
    |
    | Opciones disponibles: "lax", "strict", "none", null
    |
    | Documentación:
    | https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie
    |
    */

    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    /*
    |--------------------------------------------------------------------------
    | Cookies particionadas
    |--------------------------------------------------------------------------
    |
    | Permite vincular las cookies al sitio principal en contextos
    | donde intervienen diferentes dominios.
    |
    | Para utilizar esta opción, la cookie debe tener habilitado
    | el atributo "secure" y la política SameSite debe ser "none".
    |
    */

    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),

];
