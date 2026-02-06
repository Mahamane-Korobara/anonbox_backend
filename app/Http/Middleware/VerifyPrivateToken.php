<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
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

        // Vérifier que le header est présent
        if (!$privateToken) {
            return response()->json([
                'success' => false,
                'message' => 'Token manquant. Ajoutez le header X-Private-Token.'
            ], 401);
        }

        // Vérifier que le token existe et est valide
        $user = User::where('private_token', $privateToken)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalide ou expiré'
            ], 401);
        }

        // Ajouter l'utilisateur au request pour utilisation dans les controllers
        $request->merge(['authenticated_user' => $user]);

        return $next($request);
    }
}
