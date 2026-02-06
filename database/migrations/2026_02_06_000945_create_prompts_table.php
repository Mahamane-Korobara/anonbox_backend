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
        Schema::create('prompts', function (Blueprint $table) {
            $table->id();

            // Relation avec l'utilisateur
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade'); // Si user supprimé, ses questions aussi

            // Contenu de la question
            $table->string('question_text', 500); // Texte de la question
            $table->string('slug', 550)->nullable(); // Slug URL-friendly (optionnel)

            // Métriques de viralité
            $table->unsignedBigInteger('times_shared')->default(0); // Nb de fois partagée
            $table->unsignedBigInteger('messages_received')->default(0); // Nb de réponses reçues

            // Statut
            $table->boolean('is_active')->default(true); // Pour désactiver sans supprimer

            $table->timestamps();
            $table->softDeletes(); // Logique de fallback : redirige vers profil si supprimée

            // Index
            $table->index(['user_id', 'is_active']); // Lister les questions actives d'un user
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prompts');
    }
};
