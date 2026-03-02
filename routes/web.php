<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShareCardPageController;

/*
|--------------------------------------------------------------------------
| Web Routes - AnonBox
|--------------------------------------------------------------------------
|
| La page publique /u/{handle} est gérée par Next.js.
| Laravel redirige vers le frontend pour toutes ces URLs.
|
| NEXT_PUBLIC_APP_URL doit être défini dans .env Laravel :
|   FRONTEND_URL=http://localhost:3000
|
*/

$frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');

// Page de partage OG (hébergée sur Laravel)
Route::get('/share/{id}', [ShareCardPageController::class, 'show'])
    ->where('id', '[a-z0-9]{24}');

// Page d'accueil → Next.js
Route::get('/', function () use ($frontendUrl) {
    return redirect($frontendUrl);
});

// ─── Page publique profil → Next.js ───────────────────────────────────────
// /u/alice       → http://localhost:3000/u/alice
// /u/alice?q=2   → http://localhost:3000/u/alice?q=2
Route::get('/u/{handle}', function (string $handle) use ($frontendUrl) {
    $query = request()->getQueryString(); // preserve ?q=ID
    $url   = rtrim($frontendUrl, '/') . "/u/{$handle}";
    if ($query) $url .= "?{$query}";
    return redirect($url, 301);
});

// ─── Health check web (optionnel) ────────────────────────────────────────
Route::get('/health-web', function () {
    return response()->json(['status' => 'ok', 'layer' => 'web']);
});
