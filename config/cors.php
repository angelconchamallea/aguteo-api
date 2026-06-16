<?php

return [

    /*
     * Rutas a las que se aplican los headers CORS.
     * Solo la API pública — el panel Filament (/admin) usa sesión y no necesita CORS.
     */
    'paths' => ['api/*'],

    'allowed_methods' => ['GET', 'POST', 'OPTIONS'],

    /*
     * En desarrollo: http://localhost:3000 (Next.js dev server).
     * En producción: https://aguteobabys.cl
     * Configurar FRONTEND_URL en el .env de cada entorno.
     */
    'allowed_origins' => array_filter(
        explode(',', env('FRONTEND_URL', 'http://localhost:3000'))
    ),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'Accept', 'X-Requested-With'],

    'exposed_headers' => [],

    'max_age' => 3600,

    /*
     * false: la API no usa cookies ni sesión.
     * true solo sería necesario si usáramos autenticación basada en cookies (no es el caso).
     */
    'supports_credentials' => false,

];
