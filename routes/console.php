<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('shares:cleanup {--days=3 : Supprimer les cartes de plus de N jours}', function () {
    $days = max(1, (int) $this->option('days'));

    $metaDir = storage_path('app/share-cards-meta');
    $imagesDir = public_path('images/share-cards');

    if (!File::exists($metaDir)) {
        $this->info('Aucun dossier de métadonnées à nettoyer.');
        return self::SUCCESS;
    }

    $threshold = now()->subDays($days)->timestamp;
    $metaFiles = File::files($metaDir);

    $deletedRecords = 0;
    $deletedImages = 0;
    $errors = 0;

    foreach ($metaFiles as $metaFile) {
        try {
            $raw = File::get($metaFile->getPathname());
            $record = json_decode($raw, true);

            $createdAt = $record['created_at'] ?? null;
            $createdTs = $createdAt ? strtotime((string) $createdAt) : false;

            if ($createdTs !== false) {
                if ($createdTs >= $threshold) {
                    continue;
                }
            } else {
                if ($metaFile->getMTime() >= $threshold) {
                    continue;
                }
            }

            $id = pathinfo($metaFile->getFilename(), PATHINFO_FILENAME);

            File::delete($metaFile->getPathname());
            $deletedRecords++;

            if (File::exists($imagesDir)) {
                foreach (File::glob($imagesDir . DIRECTORY_SEPARATOR . $id . '.*') as $imagePath) {
                    File::delete($imagePath);
                    $deletedImages++;
                }
            }
        } catch (\Throwable $e) {
            $errors++;
            $this->warn('Erreur cleanup share card: ' . $e->getMessage());
        }
    }

    $this->info("Shares nettoyés: {$deletedRecords} métadonnées, {$deletedImages} images (>{$days} jours). Erreurs: {$errors}");

    return self::SUCCESS;
})->purpose('Supprime les cartes de partage expirées (fichiers + métadonnées).');
