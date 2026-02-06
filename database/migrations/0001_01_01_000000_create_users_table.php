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
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // ID Interne (clé primaire immuable)

            // Identité Publique
            $table->string('display_name', 100); // Nom d'affichage (non unique)
            $table->string('handle', 150)->unique(); // Handle unique (ex: kyle-z2p)

            // Jeton Privé (Preuve de propriété)
            $table->uuid('private_token')->unique(); // UUID pour l'accès inbox

            // Métadonnées
            $table->unsignedBigInteger('total_messages_received')->default(0);
            $table->unsignedBigInteger('total_responses_posted')->default(0);
            $table->timestamp('last_activity_at')->nullable();

            $table->timestamps();
            $table->softDeletes(); // Pour désactivation compte sans perte de données

            // Index de performance
            $table->index('handle'); // Recherche ultra-rapide par handle
            $table->index('private_token'); // Authentification rapide
            $table->index('created_at'); // Tri chronologique
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
