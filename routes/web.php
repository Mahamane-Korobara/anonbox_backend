<?php

use App\Models\User;
use App\Models\Prompt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/u/{handle}', function (Request $request, $handle) {
    // 1. Trouver l'utilisateur ou erreur 404
    $user = User::where('handle', $handle)->firstOrFail();

    // 2. Vérifier si une question spécifique est demandée (?q=ID)
    $promptId = $request->query('q');
    $prompt = null;

    if ($promptId) {
        $prompt = Prompt::where('id', $promptId)
            ->where('user_id', $user->id)
            ->first();
    }

    // 3. Retourner la vue unique
    // Si $prompt est null (supprimé ou non demandé), on affichera le formulaire par défaut
    return view('public_profile', [
        'user' => $user,
        'prompt' => $prompt
    ]);
});
