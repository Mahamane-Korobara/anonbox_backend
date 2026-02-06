<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Message;
use App\Models\RateLimit;
use App\Models\BlacklistedWord;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Requests\RespondMessageRequest;
use App\Http\Resources\MessageResource;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Envoyer un message anonyme (Public)
     */
    public function store(StoreMessageRequest $request)
    {
        // Rate Limiting
        $limitCheck = RateLimit::checkLimit($request->ip(), 'message_send', $request->user_id);
        if ($limitCheck['blocked']) {
            return response()->json($limitCheck, 429);
        }

        // Blacklist Check
        $blacklistCheck = BlacklistedWord::checkText($request->anonymous_content);
        if ($blacklistCheck['contains'] && in_array($blacklistCheck['action'], ['block', 'auto_delete'])) {
            return response()->json(['success' => false, 'message' => 'Contenu inapproprié détecté'], 403);
        }

        // Création
        $message = Message::create(array_merge($request->validated(), [
            'sender_ip' => $request->ip(),
            'sender_user_agent' => $request->header('User-Agent'),
            'status' => 'unread',
        ]));

        // Updates & Stats
        $user = User::find($request->user_id);
        $user->incrementMessagesReceived();
        if ($request->prompt_id) {
            $user->prompts()->find($request->prompt_id)?->incrementMessages();
        }

        RateLimit::recordAttempt($request->ip(), 'message_send', $request->user_id, $request->header('User-Agent'));

        return (new MessageResource($message))
            ->additional(['success' => true, 'message' => 'Message envoyé'])
            ->response()->setStatusCode(201);
    }

    /**
     * Inbox de l'utilisateur (Privé)
     */
    public function inbox(Request $request)
    {
        $user = $this->getUserByToken($request);

        $messages = $user->messages()
            ->with('prompt')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('created_at', 'desc')
            ->get();

        return MessageResource::collection($messages)->additional([
            'success' => true,
            'stats' => [
                'total' => $messages->count(),
                'unread' => $user->messages()->where('status', 'unread')->count(),
            ]
        ]);
    }

    /**
     * Marquer comme lu
     */
    public function markAsRead(Request $request, $id)
    {
        $user = $this->getUserByToken($request);
        $message = $user->messages()->findOrFail($id);

        $message->markAsRead();

        return response()->json(['success' => true, 'message' => 'Message marqué comme lu']);
    }

    /**
     * Répondre à un message
     */
    public function respond(RespondMessageRequest $request, $id)
    {
        $user = $this->getUserByToken($request);
        $message = $user->messages()->findOrFail($id);

        $message->respond($request->response_content);

        return (new MessageResource($message))->additional([
            'success' => true,
            'message' => 'Réponse enregistrée'
        ]);
    }

    /**
     * Supprimer un message
     */
    public function destroy(Request $request, $id)
    {
        $user = $this->getUserByToken($request);
        $user->messages()->findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Message supprimé']);
    }

    /**
     * Mark as shared
     */
    public function markAsShared(Request $request, $id)
    {
        $user = $this->getUserByToken($request);
        $message = $user->messages()->findOrFail($id);

        $message->markAsShared();

        return response()->json(['success' => true, 'shared_at' => $message->shared_at]);
    }

    /**
     * Helper interne pour valider le token
     */
    private function getUserByToken(Request $request)
    {
        $user = User::where('private_token', $request->header('X-Private-Token'))->first();

        if (!$user) {
            abort(response()->json(['success' => false, 'message' => 'Token invalide'], 401));
        }

        return $user;
    }
}
