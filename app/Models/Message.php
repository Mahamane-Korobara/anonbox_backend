<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'prompt_id',
        'anonymous_content',
        'response_content',
        'sender_ip',
        'sender_user_agent',
        'status',
        'is_flagged',
        'is_shared',
        'read_at',
        'responded_at',
        'shared_at',
    ];

    protected $hidden = [
        'sender_ip', // Ne JAMAIS exposer publiquement
        'sender_user_agent',
    ];

    protected $casts = [
        'is_flagged' => 'boolean',
        'is_shared' => 'boolean',
        'read_at' => 'datetime',
        'responded_at' => 'datetime',
        'shared_at' => 'datetime',
    ];

    /**
     * Relation : Destinataire du message
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation : Question d'origine (peut être null si supprimée)
     */
    public function prompt()
    {
        return $this->belongsTo(Prompt::class);
    }

    /**
     * Scope : Messages non lus
     */
    public function scopeUnread($query)
    {
        return $query->where('status', 'unread');
    }

    /**
     * Scope : Messages avec réponse
     */
    public function scopeResponded($query)
    {
        return $query->where('status', 'responded');
    }

    /**
     * Scope : Messages flaggés
     */
    public function scopeFlagged($query)
    {
        return $query->where('is_flagged', true);
    }

    /**
     * Marque le message comme lu
     */
    public function markAsRead(): void
    {
        if ($this->status === 'unread') {
            $this->update([
                'status' => 'read',
                'read_at' => now(),
            ]);
        }
    }

    /**
     * Ajoute une réponse au message
     */
    public function respond(string $responseContent): void
    {
        $this->update([
            'response_content' => $responseContent,
            'status' => 'responded',
            'responded_at' => now(),
        ]);

        // Incrémente le compteur du user
        $this->user->incrementResponsesPosted();
    }

    /**
     * Marque la réponse comme partagée (carte générée)
     */
    public function markAsShared(): void
    {
        $this->update([
            'is_shared' => true,
            'shared_at' => now(),
        ]);
    }

    /**
     * Flag le message comme abusif
     */
    public function flag(): void
    {
        $this->update(['is_flagged' => true]);
    }

    /**
     * Accessor : Vérifie si le message a une réponse
     */
    public function getHasResponseAttribute(): bool
    {
        return !is_null($this->response_content);
    }

    /**
     * Accessor : Texte de la question d'origine (avec fallback)
     */
    public function getPromptTextAttribute(): ?string
    {
        return $this->prompt?->question_text ?? 'Question supprimée';
    }
}
