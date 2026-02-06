<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class User extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'display_name',
        'handle',
        'private_token',
        'total_messages_received',
        'total_responses_posted',
        'last_activity_at',
    ];

    protected $hidden = [
        'private_token', // Ne JAMAIS exposer dans les API publiques
        'deleted_at',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
        'total_messages_received' => 'integer',
        'total_responses_posted' => 'integer',
    ];

    /**
     * Boot du modèle : génération automatique des identifiants
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // Génération du private_token unique
            if (empty($user->private_token)) {
                $user->private_token = Str::uuid();
            }

            // Génération du handle unique (display_name + suffixe aléatoire)
            if (empty($user->handle)) {
                $user->handle = self::generateUniqueHandle($user->display_name);
            }
        });
    }

    /**
     * Génère un handle unique basé sur le display_name
     */
    public static function generateUniqueHandle(string $displayName): string
    {
        $baseHandle = Str::slug($displayName);
        $handle = $baseHandle;
        $suffix = 0;

        // Boucle jusqu'à trouver un handle disponible
        while (self::where('handle', $handle)->exists()) {
            $suffix++;
            $randomSuffix = Str::random(3); // Ex: kyle-z2p
            $handle = $baseHandle . '-' . strtolower($randomSuffix);
        }

        return $handle;
    }

    /**
     * Régénère le private_token (en cas de fuite/compromission)
     */
    public function regeneratePrivateToken(): string
    {
        $this->private_token = Str::uuid();
        $this->save();

        return $this->private_token;
    }

    /**
     * Relation : Questions créées par l'utilisateur
     */
    public function prompts()
    {
        return $this->hasMany(Prompt::class);
    }

    /**
     * Relation : Messages reçus par l'utilisateur
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Scope : Messages non lus
     */
    public function scopeUnreadMessages($query)
    {
        return $this->messages()->where('status', 'unread');
    }

    /**
     * Accessor : URL publique du profil
     */
    public function getPublicUrlAttribute(): string
    {
        return url("/u/{$this->handle}");
    }

    /**
     * Accessor : URL privée de l'inbox
     */
    public function getInboxUrlAttribute(): string
    {
        return url("/inbox/{$this->private_token}");
    }

    /**
     * Incrémente le compteur de messages reçus
     */
    public function incrementMessagesReceived(): void
    {
        $this->increment('total_messages_received');
        $this->update(['last_activity_at' => now()]);
    }

    /**
     * Incrémente le compteur de réponses postées
     */
    public function incrementResponsesPosted(): void
    {
        $this->increment('total_responses_posted');
    }
}
