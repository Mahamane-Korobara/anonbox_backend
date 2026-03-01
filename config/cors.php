<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Cette configuration permet à ton frontend Next.js de communiquer 
    | avec ton API Laravel sans blocage du navigateur.
    |
    */

    // On ajoute 'login' et 'logout' s'ils ne sont pas déjà préfixés par /api
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',               // Next.js local
        'http://127.0.0.1:3000',
        'https://anonbox.sahelstack.tech',     // Ton domaine de production
        env('FRONTEND_URL'),                   // Chargement dynamique via .env
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],                // Plus simple pour éviter les erreurs de headers manquants

    'exposed_headers' => [],

    'max_age' => 0,

    /*
    | IMPORTANT : Doit être à 'true' pour que les cookies (XSRF-TOKEN, session) 
    | soient envoyés entre le frontend et l'API.
    */
    'supports_credentials' => true,

];