<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ac_verificaciones_eficacia', function (Blueprint $table) {
            $table->id();

            $table->foreignId('accion_correctiva_id')
                ->constrained('acciones_correctivas')
                ->cascadeOnDelete();

            $table->unsignedInteger('ciclo');

            $table->date('fecha_verificacion');

            $table->foreignId('verificado_por_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->boolean('criterios_cumplidos')
                ->default(false);

            $table->boolean('resultado_eficaz')
                ->default(false);

            $table->text('resultado');

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
        Schema::dropIfExists('ac_verificaciones_eficacia');
    }
};