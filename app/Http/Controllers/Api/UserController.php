<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Créer un profil
     */
    public function store(StoreUserRequest $request)
    {
        // Les données sont déjà validées ici grâce à StoreUserRequest
        $user = User::create($request->validated());

        return (new UserResource($user))
            ->additional([
                'success' => true,
                'private_token' => $user->private_token,
                'warning' => 'Sauvegarde ton lien privé ! C\'est ta seule clé d\'accès.'
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Voir le profil public
     */
    public function show($handle)
    {
        $user = User::where('handle', $handle)->firstOrFail();

        return (new UserResource($user))->additional(['success' => true]);
    }

    /**
     * Vérifier le token (Login sans mot de passe)
     */
    public function verifyToken(Request $request)
    {
        $request->validate(['private_token' => 'required|uuid']);

        $user = User::where('private_token', $request->private_token)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Token invalide'], 401);
        }

        return (new UserResource($user))->additional([
            'success' => true,
            'unread_count' => $user->messages()->where('status', 'unread')->count(),
        ]);
    }

    /**
     * Régénérer le token
     */
    public function regenerateToken(Request $request)
    {
        $request->validate(['private_token' => 'required|uuid']);

        $user = User::where('private_token', $request->private_token)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Token invalide'], 401);
        }

        $newToken = $user->regeneratePrivateToken();

        return response()->json([
            'success' => true,
            'message' => 'Nouveau token généré',
            'data' => [
                'private_token' => $newToken,
                'inbox_url' => $user->inbox_url
            ]
        ]);
    }
}
