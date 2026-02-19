<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateCardRequest;
use App\Services\CardGeneratorService;

class CardController extends Controller
{
    protected $cardService;

    // Injection de dépendance du Service
    public function __construct(CardGeneratorService $cardService)
    {
        $this->cardService = $cardService;
    }

    /**
     * Générer une carte visuelle (PNG)
     */
    public function generate(GenerateCardRequest $request)
    {
        // L'utilisateur est injecté par le Middleware VerifyPrivateToken
        $user = $request->user();

        // Récupérer le message en s'assurant qu'il appartient à l'utilisateur
        $message = $user->messages()->find($request->message_id);

        if (!$message) {
            return response()->json(['success' => false, 'message' => 'Message introuvable'], 404);
        }

        if (!$message->has_response) {
            return response()->json(['success' => false, 'message' => 'Ce message n\'a pas encore de réponse'], 422);
        }

        try {
            // Appel au Service pour la logique lourde
            $cardPath = $this->cardService->generate($message, $user);

            $message->markAsShared();

            return response()->json([
                'success' => true,
                'message' => 'Carte générée avec succès',
                'data' => [
                    'card_url' => asset($cardPath),
                    'download_url' => route('cards.download', ['filename' => basename($cardPath)]),
                    'share_text' => "Envoie-moi un message anonyme 👀 : {$user->public_url}",
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Télécharger une carte
     */
    public function download($filename)
    {
        // Sécurité basique pour empêcher de remonter les dossiers (LFI)
        $filename = basename($filename);
        $path = public_path("images/cards/{$filename}");

        if (!file_exists($path)) {
            abort(404, 'Carte introuvable');
        }

        return response()->download($path);
    }

    /**
     * Nettoyage (Idéalement à mettre dans une commande Artisan Console)
     */
    public function cleanup()
    {
        $files = glob(public_path('images/cards/*.png'));
        $deleted = 0;
        $expiry = now()->subDays(7)->timestamp;

        foreach ($files as $file) {
            if (filemtime($file) < $expiry) {
                @unlink($file);
                $deleted++;
            }
        }

        return response()->json(['success' => true, 'message' => "{$deleted} cartes supprimées"]);
    }
}
