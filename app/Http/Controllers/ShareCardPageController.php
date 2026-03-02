<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class ShareCardPageController extends Controller
{
    public function show(string $id)
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

        return response()
            ->view('share-card', [
                'card' => $card,
                'shareUrl' => url("/share/{$id}"),
            ])
            ->header('Cache-Control', 'public, max-age=600');
    }
}
