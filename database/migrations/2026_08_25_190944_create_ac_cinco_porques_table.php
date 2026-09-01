<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ac_cinco_porques', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ac_analisis_id')
                ->constrained('ac_analisis')
                ->cascadeOnDelete();

            $table->foreignId('ac_idea_id')
                ->constrained('ac_ideas')
                ->restrictOnDelete();

            $table->string('titulo', 255);

            $table->text('causa_raiz_propuesta')->nullable();

            $table->string('estado', 30)
                ->default('en_proceso');

            $table->timestamps();

            $table->index('ac_analisis_id');
            $table->index('ac_idea_id');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ac_cinco_porques');
    }
};