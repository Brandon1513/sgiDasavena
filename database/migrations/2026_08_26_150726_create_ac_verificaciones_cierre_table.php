<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ac_verificaciones_cierre', function (Blueprint $table) {
            $table->id();

            $table->foreignId('accion_correctiva_id')
                ->constrained('acciones_correctivas')
                ->cascadeOnDelete();

            $table->unsignedInteger('ciclo');

            $table->date('fecha_verificacion');

            $table->foreignId('verificado_por_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->boolean('acciones_implementadas')
                ->default(false);

            $table->boolean('evidencias_completas')
                ->default(false);

            $table->boolean('implementacion_conforme')
                ->default(false);

            $table->text('resultado')->nullable();

            $table->enum('resultado_verificacion', [
                'aprobada',
                'rechazada',
            ])->default('rechazada');

            $table->text('observaciones')->nullable();

            $table->timestamps();

            $table->index([
                'accion_correctiva_id',
                'ciclo',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ac_verificaciones_cierre');
    }
};