<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ac_actividades', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ac_plan_accion_id')
                ->constrained('ac_planes_accion')
                ->cascadeOnDelete();

            $table->text('descripcion');

            $table->foreignId('responsable_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->date('fecha_compromiso');

            $table->date('fecha_cumplimiento')
                ->nullable();

            $table->string('estado', 30)
                ->default('pendiente');

            $table->text('observaciones')
                ->nullable();

            $table->timestamps();

            $table->index('responsable_id');
            $table->index('estado');
            $table->index('fecha_compromiso');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ac_actividades');
    }
};