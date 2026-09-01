<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ac_causas_raiz', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ac_analisis_id')
                ->constrained('ac_analisis')
                ->cascadeOnDelete();

            $table->foreignId('ac_cinco_porque_id')
                ->constrained('ac_cinco_porques')
                ->restrictOnDelete();

            $table->text('descripcion');

            $table->foreignId('propuesta_por_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->date('fecha_propuesta');

            $table->string('estado_validacion', 30)
                ->default('pendiente');

            $table->foreignId('validado_por_id')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();

            $table->date('fecha_validacion')->nullable();

            $table->text('comentarios_validacion')->nullable();

            $table->timestamps();

            $table->index('ac_analisis_id');
            $table->index('ac_cinco_porque_id');
            $table->index('estado_validacion');
            $table->index('validado_por_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ac_causas_raiz');
    }
};