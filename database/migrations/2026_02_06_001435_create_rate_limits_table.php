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
        Schema::create('rate_limits', function (Blueprint $table) {
            $table->id();

            // Identification de l'origine
            $table->ipAddress('ip_address');
            $table->foreignId('user_id')->nullable() // Cible du spam (optionnel)
                ->constrained('users')
                ->onDelete('cascade');

            // Compteurs
            $table->unsignedInteger('attempts')->default(1);
            $table->timestamp('last_attempt_at');
            $table->timestamp('blocked_until')->nullable(); // Temps de blocage

            // Métadonnées
            $table->string('user_agent', 500)->nullable();
            $table->enum('action_type', ['message_send', 'account_create', 'prompt_create'])
                ->default('message_send');

            $table->timestamps();

            // Index composites pour vérification rapide
            $table->unique(['ip_address', 'user_id', 'action_type'], 'rate_limit_unique');
            $table->index(['ip_address', 'blocked_until']);
            $table->index('last_attempt_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rate_limits');
    }
};
