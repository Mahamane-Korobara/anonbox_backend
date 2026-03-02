<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShareCardRequest;
use Illuminate\Support\Str;

class ShareCardController extends Controller
{
    public function store(StoreShareCardRequest $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalide',
            ], 401);
        }

        try {
            $id = Str::lower(Str::random(24));

            $image = $request->file('image');
            $extension = strtolower($image->getClientOriginalExtension() ?: 'png');
            if (!in_array($extension, ['png', 'jpg', 'jpeg', 'webp'], true)) {
                $extension = 'png';
            }

            $imagesDir = public_path('images/share-cards');
            $metaDir = storage_path('app/share-cards-meta');

            if (!is_dir($imagesDir)) {
                mkdir($imagesDir, 0755, true);
            }
            if (!is_dir($metaDir)) {
                mkdir($metaDir, 0755, true);
            }

            $filename = "{$id}.{$extension}";
            $image->move($imagesDir, $filename);

            $isMessage = filter_var($request->input('isMessage', false), FILTER_VALIDATE_BOOLEAN);
            $shareText = trim((string) $request->input('shareText', ''));
            $cardText = trim((string) $request->input('cardText', ''));
            $targetUrl = trim((string) $request->input('targetUrl', ''));

            $description = Str::limit(preg_replace('/\s+/', ' ', $shareText), 220, '…');
            $title = $isMessage ? 'Message anonyme AnonBox' : 'Question anonyme AnonBox';

            $record = [
                'id' => $id,
                'title' => $title,
                'description' => $description,
                'card_text' => Str::limit($cardText, 500, '…'),
                'target_url' => $targetUrl,
                'is_message' => $isMessage,
                'image_url' => url("/images/share-cards/{$filename}"),
                'created_at' => now()->toIso8601String(),
                'owner' => [
                    'id' => $user->id,
                    'handle' => $user->handle,
                ],
            ];

            file_put_contents(
                "{$metaDir}/{$id}.json",
                json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $id,
                    'share_page_url' => url("/share/{$id}"),
                    'image_url' => $record['image_url'],
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de préparer le partage.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
