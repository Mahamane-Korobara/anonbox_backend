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
        Schema::create('blacklisted_words', function (Blueprint $table) {
            $table->id();

            // Mot ou expression à filtrer
            $table->string('word', 255)->unique();

            // Niveau de sévérité
            $table->enum('severity', ['mild', 'moderate', 'severe'])
                ->default('moderate');

            // Action automatique
            $table->enum('action', ['warn', 'block', 'auto_delete'])
                ->default('block');

            // Statistiques
            $table->unsignedBigInteger('times_triggered')->default(0);

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Index
            $table->index(['is_active', 'severity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blacklisted_words');
    }
};
