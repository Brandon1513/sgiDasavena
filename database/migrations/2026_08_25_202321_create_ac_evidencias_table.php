<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ac_evidencias', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ac_actividad_id')
                ->constrained('ac_actividades')
                ->cascadeOnDelete();

            $table->foreignId('subido_por_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('nombre_original');

            $table->string('ruta');

            $table->string('disco')
                ->default('public');

            $table->string('mime_type')
                ->nullable();

            $table->unsignedBigInteger('tamano')
                ->nullable();

            $table->text('descripcion')
                ->nullable();

            $table->timestamps();

            $table->index('subido_por_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ac_evidencias');
    }
};