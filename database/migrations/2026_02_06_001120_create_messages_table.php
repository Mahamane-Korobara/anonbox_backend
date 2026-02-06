<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            // Relation avec le destinataire (user qui reçoit le message)
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade'); // Si user supprimé, ses messages aussi

            // Relation avec la question (NULLABLE car peut être supprimée)
            $table->foreignId('prompt_id')
                ->nullable()
                ->constrained('prompts')
                ->onDelete('set null'); // Si prompt supprimée, message reste mais prompt_id = null

            // Contenu anonyme
            $table->text('anonymous_content'); // Message envoyé (max 1000 chars, validé en backend)
            $table->text('response_content')->nullable(); // Réponse du user (optionnelle)

            // Métadonnées de tracking (sans révéler l'identité)
            $table->ipAddress('sender_ip')->nullable(); // Pour rate limiting (jamais affichée)
            $table->string('sender_user_agent', 500)->nullable(); // Pour détection bots

            // Statut du message
            $table->enum('status', ['unread', 'read', 'responded', 'archived'])
                ->default('unread');
            $table->boolean('is_flagged')->default(false); // Pour signalement contenu abusif
            $table->boolean('is_shared')->default(false); // Si la réponse a été partagée en carte

            // Horodatage
            $table->timestamp('read_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('shared_at')->nullable();

            $table->timestamps();
            $table->softDeletes(); // Suppression logique (garde historique)

            // Index de performance critiques
            $table->index(['user_id', 'status', 'created_at']); // Inbox filtering
            $table->index('sender_ip'); // Rate limiting par IP
            $table->index(['user_id', 'prompt_id']); // Messages par question
            $table->index('created_at'); // Tri chronologique
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
