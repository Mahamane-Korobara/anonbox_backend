<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerifyPrivateToken
{
    /**
     * Middleware pour vérifier le private_token dans les headers
     * 
     * Usage dans les routes : ->middleware('verify.private.token')
     * Header attendu : X-Private-Token: uuid-here
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $privateToken = $request->header('X-Private-Token');

        if (!$privateToken) {
            return response()->json([
                'success' => false,
                'message' => 'Token manquant. Ajoutez le header X-Private-Token.'
            ], 401);
        }

        $user = User::where('private_token', $privateToken)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalide ou expiré'
            ], 401);
        }

        // --- LA LIGNE MAGIQUE ---
        // On définit l'utilisateur pour la session actuelle de la requête
        Auth::login($user);

        // Optionnel : tu peux garder le merge si tu y tiens, 
        // mais setUser suffit pour faire marcher $request->user()
        $request->merge(['authenticated_user' => $user]);

        return $next($request);
    }
}
