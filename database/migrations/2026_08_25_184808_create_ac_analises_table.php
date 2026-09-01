<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ac_analisis', function (Blueprint $table) {
            $table->id();

            $table->foreignId('accion_correctiva_id')
                ->constrained('acciones_correctivas')
                ->cascadeOnDelete();

            $table->unsignedInteger('ciclo');

            $table->date('fecha_inicio');

            $table->date('fecha_cierre')->nullable();

            $table->foreignId('responsable_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('estado', 30)->default('en_proceso');

            $table->text('observaciones')->nullable();

            $table->timestamps();

            $table->unique([
                'accion_correctiva_id',
                'ciclo',
            ]);

            $table->index('responsable_id');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ac_analisis');
    }
};