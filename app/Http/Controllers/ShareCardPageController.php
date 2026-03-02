<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ShareCardPageController extends Controller
{
    public function show(Request $request, string $id)
    {
        if (!preg_match('/^[a-z0-9]{24}$/', $id)) {
            abort(404);
        }

        $path = storage_path("app/share-cards-meta/{$id}.json");
        if (!File::exists($path)) {
            abort(404);
        }

        $card = json_decode(File::get($path), true);
        if (!is_array($card)) {
            abort(404);
        }

        $targetUrl = $card['target_url'] ?? null;

        // On sert la page OG uniquement aux bots (WhatsApp/Facebook/X...) ou si preview=1.
        $forcePreview = $request->query('preview') === '1';
        if (!$forcePreview && $targetUrl && !$this->isPreviewCrawler($request->userAgent())) {
            return redirect()->away($targetUrl, 302);
        }

        return response()
            ->view('share-card', [
                'card' => $card,
                'shareUrl' => url("/share/{$id}"),
            ])
            ->header('Cache-Control', 'public, max-age=600');
    }

    private function isPreviewCrawler(?string $userAgent): bool
    {
        if (!$userAgent) {
            return false;
        }

        $ua = strtolower($userAgent);
        $needles = [
            'whatsapp',
            'facebookexternalhit',
            'facebot',
            'twitterbot',
            'linkedinbot',
            'telegrambot',
            'slackbot',
            'discordbot',
            'skypeuripreview',
            'crawler',
            'spider',
            'bot',
        ];

        foreach ($needles as $needle) {
            if (str_contains($ua, $needle)) {
                return true;
            }
        }

        return false;
    }
}
