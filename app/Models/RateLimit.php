<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateLimit extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'user_id',
        'attempts',
        'last_attempt_at',
        'blocked_until',
        'user_agent',
        'action_type',
    ];

    protected $casts = [
        'last_attempt_at' => 'datetime',
        'blocked_until' => 'datetime',
        'attempts' => 'integer',
    ];

    /**
     * Configuration des limites par type d'action
     */
    const LIMITS = [
        'message_send' => [
            'max_attempts' => 3,      // 3 messages
            'window_minutes' => 1,    // par minute
            'block_minutes' => 15,    // blocage de 15 min si dépassé
        ],
        'account_create' => [
            'max_attempts' => 5,
            'window_minutes' => 60,
            'block_minutes' => 120,
        ],
        'prompt_create' => [
            'max_attempts' => 10,
            'window_minutes' => 60,
            'block_minutes' => 30,
        ],
    ];

    /**
     * Relation : Utilisateur cible (optionnelle)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Vérifie si une IP est bloquée pour une action
     * 
     * @param string $ipAddress
     * @param string $actionType
     * @param int|null $userId
     * @return array ['blocked' => bool, 'reason' => string|null, 'retry_after' => int|null]
     */
    public static function checkLimit(string $ipAddress, string $actionType = 'message_send', ?int $userId = null): array
    {
        $rateLimit = self::where('ip_address', $ipAddress)
            ->where('action_type', $actionType)
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->first();

        // Si aucun enregistrement, l'IP est clean
        if (!$rateLimit) {
            return ['blocked' => false, 'reason' => null, 'retry_after' => null];
        }

        // Vérifier si l'IP est actuellement bloquée
        if ($rateLimit->blocked_until && $rateLimit->blocked_until->isFuture()) {
            return [
                'blocked' => true,
                'reason' => 'Trop de tentatives. Veuillez réessayer plus tard.',
                'retry_after' => $rateLimit->blocked_until->diffInSeconds(now()),
            ];
        }

        // Vérifier le nombre de tentatives dans la fenêtre de temps
        $config = self::LIMITS[$actionType];
        $windowStart = now()->subMinutes($config['window_minutes']);

        if ($rateLimit->last_attempt_at < $windowStart) {
            // Fenêtre expirée, réinitialiser le compteur
            $rateLimit->update([
                'attempts' => 0,
                'blocked_until' => null,
            ]);
            return ['blocked' => false, 'reason' => null, 'retry_after' => null];
        }

        // Vérifier si la limite est atteinte
        if ($rateLimit->attempts >= $config['max_attempts']) {
            $blockedUntil = now()->addMinutes($config['block_minutes']);
            $rateLimit->update(['blocked_until' => $blockedUntil]);

            return [
                'blocked' => true,
                'reason' => "Limite atteinte : {$config['max_attempts']} tentatives par {$config['window_minutes']} minute(s).",
                'retry_after' => $blockedUntil->diffInSeconds(now()),
            ];
        }

        return ['blocked' => false, 'reason' => null, 'retry_after' => null];
    }

    /**
     * Enregistre une tentative pour une IP
     */
    public static function recordAttempt(string $ipAddress, string $actionType = 'message_send', ?int $userId = null, ?string $userAgent = null): void
    {
        $rateLimit = self::firstOrCreate(
            [
                'ip_address' => $ipAddress,
                'action_type' => $actionType,
                'user_id' => $userId,
            ],
            [
                'attempts' => 0,
                'last_attempt_at' => now(),
                'user_agent' => $userAgent,
            ]
        );

        $config = self::LIMITS[$actionType];
        $windowStart = now()->subMinutes($config['window_minutes']);

        // Si la dernière tentative est dans la fenêtre, incrémenter
        if ($rateLimit->last_attempt_at >= $windowStart) {
            $rateLimit->increment('attempts');
        } else {
            // Sinon, réinitialiser le compteur
            $rateLimit->update(['attempts' => 1]);
        }

        $rateLimit->update(['last_attempt_at' => now()]);
    }

    /**
     * Nettoie les enregistrements expirés (à exécuter via cron)
     */
    public static function cleanExpired(): int
    {
        return self::where('last_attempt_at', '<', now()->subHours(24))
            ->where(fn($q) => $q->whereNull('blocked_until')->orWhere('blocked_until', '<', now()))
            ->delete();
    }
}
