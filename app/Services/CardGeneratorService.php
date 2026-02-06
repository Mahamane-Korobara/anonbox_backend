<?php

namespace App\Services;

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class CardGeneratorService
{
    /**
     * Génère l'image de la carte (PNG 1080x1920)
     */
    public function generate(Message $message, User $user): string
    {
        // Configuration
        $width = 1080;
        $height = 1920;
        $padding = 80;

        // Création du canvas
        $image = Image::create($width, $height);
        $image->fill('linear-gradient(135deg, #667eea 0%, #764ba2 100%)');

        // Header (Logo + Handle)
        $this->drawHeader($image, $user, $width);

        // Message (Question)
        $messageY = 400;
        $this->drawBubble($image, 'Message anonyme', $message->anonymous_content, $messageY, $width, $padding, '#FFFFFF', '#333333');

        // Réponse
        $responseY = $messageY + 480;
        $this->drawBubble($image, 'Ma réponse', $message->response_content, $responseY, $width, $padding, 'rgba(102, 126, 234, 0.15)', '#1a202c', true);

        // Footer (CTA)
        $this->drawFooter($image, $user, $width, $height);

        // auvegarde
        $filename = 'card_' . $user->id . '_' . $message->id . '_' . Str::random(8) . '.png';
        $savePath = public_path("images/cards/{$filename}");

        // S'assurer que le dossier existe
        if (!file_exists(dirname($savePath))) {
            mkdir(dirname($savePath), 0755, true);
        }

        $image->save($savePath, 90);

        return "images/cards/{$filename}";
    }

    // --- Helpers privés pour alléger le code principal ---

    private function drawHeader($image, $user, $width)
    {
        $image->text('AnonBox', $width / 2, 150, function ($font) {
            $font->file(public_path('fonts/Poppins-Bold.ttf'));
            $font->size(60);
            $font->color('#FFFFFF');
            $font->align('center');
            $font->valign('top');
        });

        $image->text("@{$user->handle}", $width / 2, 240, function ($font) {
            $font->file(public_path('fonts/Poppins-Medium.ttf'));
            $font->size(36);
            $font->color('#E0E0E0');
            $font->align('center');
            $font->valign('top');
        });
    }

    private function drawBubble($image, $label, $content, $y, $width, $padding, $bgColor, $textColor, $border = false)
    {
        $image->drawRectangle($padding, $y, $width - $padding, $y + 400, function ($draw) use ($bgColor, $border) {
            $draw->background($bgColor);
            if ($border) $draw->border(3, '#667eea');
        });

        // Icone & Label
        $image->text('💬', $padding + 40, $y + 60, fn($f) => $f->size(48));

        $image->text($label, $padding + 120, $y + 70, function ($font) {
            $font->file(public_path('fonts/Poppins-SemiBold.ttf'));
            $font->size(28);
            $font->color('#667eea');
            $font->align('left');
            $font->valign('top');
        });

        // Texte contenu
        $wrapped = wordwrap($content, 35, "\n", true);
        $image->text($wrapped, $padding + 40, $y + 150, function ($font) use ($textColor) {
            $font->file(public_path('fonts/Poppins-Regular.ttf'));
            $font->size(32);
            $font->color($textColor);
            $font->align('left');
            $font->valign('top');
            $font->lineHeight(1.5);
        });
    }

    private function drawFooter($image, $user, $width, $height)
    {
        $footerY = $height - 200;

        $image->text('Envoie-moi un message anonyme 👀', $width / 2, $footerY, function ($font) {
            $font->file(public_path('fonts/Poppins-SemiBold.ttf'));
            $font->size(36);
            $font->color('#FFFFFF');
            $font->align('center');
        });

        $image->text($user->public_url, $width / 2, $footerY + 70, function ($font) {
            $font->file(public_path('fonts/Poppins-Bold.ttf'));
            $font->size(32);
            $font->color('#FFD700');
            $font->align('center');
        });
    }
}
