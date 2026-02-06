<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlacklistedWord extends Model
{
    use HasFactory;

    protected $fillable = [
        'word',
        'severity',
        'action',
        'times_triggered',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'times_triggered' => 'integer',
    ];

    /**
     * Scope : Mots actifs uniquement
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Vérifie si un texte contient des mots blacklistés
     * 
     * @param string $text
     * @return array ['contains' => bool, 'matches' => array, 'action' => string]
     */
    public static function checkText(string $text): array
    {
        $activeWords = self::active()->get();
        $matches = [];
        $highestAction = 'warn';

        foreach ($activeWords as $blacklistedWord) {
            // Recherche insensible à la casse avec délimiteurs
            if (preg_match('/\b' . preg_quote($blacklistedWord->word, '/') . '\b/i', $text)) {
                $matches[] = [
                    'word' => $blacklistedWord->word,
                    'severity' => $blacklistedWord->severity,
                    'action' => $blacklistedWord->action,
                ];

                // Incrémente le compteur
                $blacklistedWord->increment('times_triggered');

                // Détermine l'action la plus sévère
                if ($blacklistedWord->action === 'auto_delete') {
                    $highestAction = 'auto_delete';
                } elseif ($blacklistedWord->action === 'block' && $highestAction !== 'auto_delete') {
                    $highestAction = 'block';
                }
            }
        }

        return [
            'contains' => !empty($matches),
            'matches' => $matches,
            'action' => $highestAction,
        ];
    }

    /**
     * Ajoute un mot à la blacklist
     */
    public static function addWord(string $word, string $severity = 'moderate', string $action = 'block'): self
    {
        return self::create([
            'word' => strtolower(trim($word)),
            'severity' => $severity,
            'action' => $action,
        ]);
    }
}
