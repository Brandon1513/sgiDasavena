<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ac_contenciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('accion_correctiva_id')
                ->constrained('acciones_correctivas')
                ->cascadeOnDelete();

            $table->text('descripcion');

            $table->foreignId('responsable_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->date('fecha_implementacion');

            $table->string('estado', 30)->default('pendiente');

            $table->text('observaciones')->nullable();

            $table->timestamps();

            $table->index('accion_correctiva_id');
            $table->index('responsable_id');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ac_contenciones');
    }
};