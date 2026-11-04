
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Servicio de correo predeterminado
    |--------------------------------------------------------------------------
    |
    | Esta opción determina qué servicio utilizará Laravel para enviar
    | correos electrónicos.
    |
    | El servicio se selecciona mediante la variable MAIL_MAILER
    | del archivo .env.
    |
    | Para Destiny Store utilizaremos Resend.
    |
    */

    'default' => env('MAIL_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Configuración de los servicios de correo
    |--------------------------------------------------------------------------
    |
    | Aquí se definen los diferentes servicios disponibles para enviar
    | correos electrónicos desde la aplicación.
    |
    | Aunque existan varios servicios configurados, Laravel utilizará
    | únicamente el seleccionado en MAIL_MAILER, salvo que se indique
    | explícitamente otro.
    |
    | Servicios compatibles: smtp, sendmail, mailgun, ses, ses-v2,
    | postmark, resend, log, array, failover y roundrobin.
    |
    */

    'mailers' => [

        // Servicio SMTP tradicional.
        'smtp' => [
            'transport' => 'smtp',
            'scheme' => env('MAIL_SCHEME'),
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env(
                'MAIL_EHLO_DOMAIN',
                parse_url(
                    env('APP_URL', 'http://localhost'),
                    PHP_URL_HOST
                )
            ),
        ],

        // Servicio de correo de Amazon.
        'ses' => [
            'transport' => 'ses',
        ],

        // Servicio Postmark.
        'postmark' => [
            'transport' => 'postmark',

            // Configuraciones opcionales:
            // 'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        // Servicio utilizado por Destiny Store.
        'resend' => [
            'transport' => 'resend',
        ],

        // Envío mediante el programa Sendmail del servidor.
        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env(
                'MAIL_SENDMAIL_PATH',
                '/usr/sbin/sendmail -bs -i'
            ),
        ],

        // Guarda los correos en los registros sin enviarlos.
        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        // Almacena temporalmente los correos en memoria.
        'array' => [
            'transport' => 'array',
        ],

        // Utiliza un servicio alternativo si el principal falla.
        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
            'retry_after' => 60,
        ],

        // Distribuye los envíos entre varios servicios.
        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
            'retry_after' => 60,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Remitente predeterminado
    |--------------------------------------------------------------------------
    |
    | Todos los correos enviados por Destiny Store utilizarán esta
    | dirección y este nombre como remitente, salvo que se especifique
    | otro remitente para un correo particular.
    |
    | Durante las pruebas utilizaremos onboarding@resend.dev.
    | Cuando tengamos un dominio verificado podremos reemplazarlo
    | por una dirección propia de Destiny Store.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Destiny Store'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Modo demostración de Destiny Store
    |--------------------------------------------------------------------------
    |
    | Permite probar el envío de comprobantes sin utilizar el correo
    | electrónico real del comprador.
    |
    | Si MAIL_DEMO_MODE es true:
    | Los comprobantes se enviarán a MAIL_DEMO_ADDRESS.
    |
    | Si MAIL_DEMO_MODE es false:
    | Los comprobantes se enviarán al correo del comprador.
    |
    | Esta configuración será utilizada por el sistema encargado
    | de enviar las confirmaciones de compra.
    |
    */

    'demo' => [
        'enabled' => env('MAIL_DEMO_MODE', false),
        'address' => env('MAIL_DEMO_ADDRESS'),
    ],

];
