<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Prompt;
use App\Http\Requests\StorePromptRequest;
use App\Http\Resources\PromptResource;
use Illuminate\Http\Request;

class PromptController extends Controller
{
    /**
     * Lister les prompts actifs d'un utilisateur
     */
    public function index($handle)
    {
        $user = User::where('handle', $handle)->firstOrFail();

        $prompts = $user->prompts()
            ->active()
            ->orderBy('created_at', 'desc')
            ->get();

        return PromptResource::collection($prompts)->additional([
            'success' => true
        ]);
    }

    /**
     * Récupérer un prompt spécifique
     */
    public function show($id)
    {
        $prompt = Prompt::with('user')->find($id);

        if (!$prompt || !$prompt->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Question introuvable ou supprimée',
                'redirect_to' => $prompt ? $prompt->user->public_url : null
            ], 404);
        }

        return (new PromptResource($prompt))->additional(['success' => true]);
    }

    /**
     * Créer un nouveau prompt
     */
    public function store(StorePromptRequest $request)
    {
        $user = User::where('private_token', $request->header('X-Private-Token'))->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Token invalide'], 401);
        }

        $prompt = $user->prompts()->create($request->validated());

        return (new PromptResource($prompt))
            ->additional(['success' => true, 'message' => 'Question créée avec succès'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Supprimer un prompt (soft delete)
     */
    public function destroy(Request $request, $id)
    {
        $user = User::where('private_token', $request->header('X-Private-Token'))->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Token invalide'], 401);
        }

        $prompt = $user->prompts()->find($id);

        if (!$prompt) {
            return response()->json(['success' => false, 'message' => 'Question introuvable'], 404);
        }

        $prompt->delete();

        return response()->json([
            'success' => true,
            'message' => 'Question supprimée (les liens redirigent vers ton profil)'
        ]);
    }

    /**
     * Incrémenter le compteur de partages
     */
    public function incrementShare($id)
    {
        $prompt = Prompt::findOrFail($id);
        $prompt->incrementShares();

        return response()->json([
            'success' => true,
            'message' => 'Partage comptabilisé'
        ]);
    }
}
