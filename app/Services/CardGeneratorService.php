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

        // --- CRÉATION DU CANVAS V3 ---
        // On crée une image de base 1x1 qu'on resize (méthode la plus fiable en v3 pour un canvas vide)
        $image = Image::read(base64_decode("iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg=="))
            ->resize($width, $height);

        // Remplissage du fond (Couleur solide recommandée pour GD v3)
        $image->fill('#667eea');

        // Header (Logo + Handle)
        $this->drawHeader($image, $user, $width);

        // Message (Question)
        $messageY = 400;
        $this->drawBubble($image, 'Message anonyme', $message->anonymous_content, $messageY, $width, $padding, '#FFFFFF', '#333333');

        // Réponse
        $responseY = $messageY + 500;
        $this->drawBubble($image, 'Ma réponse', $message->response_content, $responseY, $width, $padding, '#f8fafc', '#1a202c', true);

        // Footer (CTA)
        $this->drawFooter($image, $user, $width, $height);

        // Sauvegarde
        $filename = 'card_' . $user->id . '_' . $message->id . '_' . Str::random(8) . '.png';
        $directory = public_path("images/cards");
        $savePath = $directory . "/{$filename}";

        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // En v3, on encode avant de sauvegarder
        $image->toPng()->save($savePath);

        return "images/cards/{$filename}";
    }

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
        // En v3, drawRectangle utilise une closure pour définir la taille et le style
        $image->drawRectangle($padding, $y, function ($draw) use ($width, $padding, $bgColor, $border) {
            $draw->size($width - ($padding * 2), 420); // Largeur et Hauteur
            $draw->background($bgColor);
            if ($border) {
                $draw->border('#4c51bf', 4);
            }
        });

        // Label
        $image->text($label, $padding + 60, $y + 50, function ($font) {
            $font->file(public_path('fonts/Poppins-SemiBold.ttf'));
            $font->size(30);
            $font->color('#667eea');
            $font->valign('top');
        });

        // Texte contenu (wordwrap pour éviter que le texte sorte de la bulle)
        $wrapped = wordwrap($content, 35, "\n", true);
        $image->text($wrapped, $padding + 60, $y + 130, function ($font) use ($textColor) {
            $font->file(public_path('fonts/Poppins-Regular.ttf'));
            $font->size(34);
            $font->color($textColor);
            $font->valign('top');
            $font->lineHeight(1.6);
        });
    }

    private function drawFooter($image, $user, $width, $height)
    {
        $footerY = $height - 250;

        $image->text('Envoie-moi un message anonyme 👀', $width / 2, $footerY, function ($font) {
            $font->file(public_path('fonts/Poppins-SemiBold.ttf'));
            $font->size(38);
            $font->color('#FFFFFF');
            $font->align('center');
        });

        $image->text($user->public_url, $width / 2, $footerY + 80, function ($font) {
            $font->file(public_path('fonts/Poppins-Bold.ttf'));
            $font->size(34);
            $font->color('#FFD700');
            $font->align('center');
        });
    }
}
