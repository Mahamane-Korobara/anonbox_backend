<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BlacklistedWord;

class BlacklistedWordSeeder extends Seeder
{
    /**
     * Seed the application's database with initial blacklisted words.
     */
    public function run(): void
    {
        $this->command->info('🚫 Initialisation de la blacklist...');

        $words = [
            // ==================================
            // NIVEAU MILD (Avertissement)
            // ==================================
            [
                'word' => 'spam',
                'severity' => 'mild',
                'action' => 'warn',
            ],
            [
                'word' => 'pub',
                'severity' => 'mild',
                'action' => 'warn',
            ],
            [
                'word' => 'publicité',
                'severity' => 'mild',
                'action' => 'warn',
            ],

            // ==================================
            // NIVEAU MODERATE (Blocage)
            // ==================================
            [
                'word' => 'viagra',
                'severity' => 'moderate',
                'action' => 'block',
            ],
            [
                'word' => 'casino',
                'severity' => 'moderate',
                'action' => 'block',
            ],
            [
                'word' => 'crypto',
                'severity' => 'moderate',
                'action' => 'block',
            ],
            [
                'word' => 'bitcoin',
                'severity' => 'moderate',
                'action' => 'block',
            ],

            // ==================================
            // NIVEAU SEVERE (Suppression auto)
            // ==================================

            // Insultes basiques (exemples génériques)
            [
                'word' => 'connard',
                'severity' => 'severe',
                'action' => 'auto_delete',
            ],
            [
                'word' => 'salope',
                'severity' => 'severe',
                'action' => 'auto_delete',
            ],
            [
                'word' => 'pute',
                'severity' => 'severe',
                'action' => 'auto_delete',
            ],

            // Mots liés au harcèlement
            [
                'word' => 'suicide',
                'severity' => 'severe',
                'action' => 'auto_delete',
            ],
            [
                'word' => 'kill yourself',
                'severity' => 'severe',
                'action' => 'auto_delete',
            ],
            [
                'word' => 'tue toi',
                'severity' => 'severe',
                'action' => 'auto_delete',
            ],

            // Contenu sexuel explicite
            [
                'word' => 'porn',
                'severity' => 'severe',
                'action' => 'auto_delete',
            ],
            [
                'word' => 'sex tape',
                'severity' => 'severe',
                'action' => 'auto_delete',
            ],

            // Menaces de violence
            [
                'word' => 'je vais te tuer',
                'severity' => 'severe',
                'action' => 'auto_delete',
            ],
            [
                'word' => 'bomb',
                'severity' => 'severe',
                'action' => 'auto_delete',
            ],

            // ==================================
            // PROTECTION CONTRE LE BOT SPAM
            // ==================================
            [
                'word' => 'click here',
                'severity' => 'moderate',
                'action' => 'block',
            ],
            [
                'word' => 'cliquez ici',
                'severity' => 'moderate',
                'action' => 'block',
            ],
            [
                'word' => 'http://',
                'severity' => 'moderate',
                'action' => 'block',
            ],
            [
                'word' => 'https://',
                'severity' => 'moderate',
                'action' => 'block',
            ],
            [
                'word' => 'www.',
                'severity' => 'moderate',
                'action' => 'block',
            ],
            [
                'word' => '.com',
                'severity' => 'moderate',
                'action' => 'block',
            ],
            [
                'word' => 'telegram',
                'severity' => 'moderate',
                'action' => 'block',
            ],
            [
                'word' => 'whatsapp',
                'severity' => 'moderate',
                'action' => 'block',
            ],

            // ==================================
            // TENTATIVES DE PHISHING
            // ==================================
            [
                'word' => 'password',
                'severity' => 'severe',
                'action' => 'auto_delete',
            ],
            [
                'word' => 'mot de passe',
                'severity' => 'severe',
                'action' => 'auto_delete',
            ],
            [
                'word' => 'bank account',
                'severity' => 'severe',
                'action' => 'auto_delete',
            ],
            [
                'word' => 'compte bancaire',
                'severity' => 'severe',
                'action' => 'auto_delete',
            ],
            [
                'word' => 'credit card',
                'severity' => 'severe',
                'action' => 'auto_delete',
            ],
            [
                'word' => 'carte bancaire',
                'severity' => 'severe',
                'action' => 'auto_delete',
            ],
        ];

        // Insertion en base
        foreach ($words as $word) {
            BlacklistedWord::updateOrCreate(
                ['word' => strtolower($word['word'])],
                [
                    'severity' => $word['severity'],
                    'action' => $word['action'],
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('✅ ' . count($words) . ' mots blacklistés insérés avec succès');
        $this->command->line('');
        $this->command->line('📊 Répartition par niveau :');
        $this->command->line('   - Mild (warn) : ' . BlacklistedWord::where('severity', 'mild')->count());
        $this->command->line('   - Moderate (block) : ' . BlacklistedWord::where('severity', 'moderate')->count());
        $this->command->line('   - Severe (auto_delete) : ' . BlacklistedWord::where('severity', 'severe')->count());
    }
}
