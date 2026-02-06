<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Prompt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'question_text',
        'slug',
        'times_shared',
        'messages_received',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'times_shared' => 'integer',
        'messages_received' => 'integer',
    ];

    /**
     * Boot du modèle : génération automatique du slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($prompt) {
            if (empty($prompt->slug)) {
                $prompt->slug = Str::slug($prompt->question_text);
            }
        });
    }

    /**
     * Relation : Utilisateur propriétaire de la question
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation : Messages reçus via cette question
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Scope : Questions actives uniquement
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->whereNull('deleted_at');
    }

    /**
     * Accessor : URL publique de la question (pour partage)
     */
    public function getShareUrlAttribute(): string
    {
        return url("/u/{$this->user->handle}?q={$this->id}");
    }

    /**
     * Incrémente le compteur de partages
     */
    public function incrementShares(): void
    {
        $this->increment('times_shared');
    }

    /**
     * Incrémente le compteur de messages reçus
     */
    public function incrementMessages(): void
    {
        $this->increment('messages_received');
    }

    /**
     * Vérifie si la question a été supprimée (logique de fallback)
     */
    public function isDeleted(): bool
    {
        return !is_null($this->deleted_at);
    }
}
