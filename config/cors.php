<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration CORS pour permettre les requêtes depuis Next.js
    | À adapter selon votre domaine de production
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',           // Next.js dev
        'http://127.0.0.1:3000',           // Next.js dev alternative
        'https://anonbox.com',             // Production frontend
        'https://www.anonbox.com',         // Production frontend avec www
        // Ajoutez les autres domaines ici
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Content-Type',
        'X-Requested-With',
        'Authorization',
        'X-Private-Token',                 // Header personnalisé pour l'authentification
        'Accept',
        'Origin',
    ],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
