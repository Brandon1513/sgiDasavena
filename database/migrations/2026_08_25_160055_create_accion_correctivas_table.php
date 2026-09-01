<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acciones_correctivas', function (Blueprint $table) {
            $table->id();

            $table->string('codigo', 20)->unique();

            $table->date('fecha_apertura');

            $table->foreignId('origen_id')
                ->constrained('origenes_acciones_correctivas')
                ->restrictOnDelete();

            $table->foreignId('responsable_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('estado_id')
                ->constrained('estados_acciones_correctivas')
                ->restrictOnDelete();

            $table->text('descripcion');

            $table->decimal('porcentaje_avance', 5, 2)
                ->default(0);

            $table->date('fecha_cierre')
                ->nullable();

            $table->unsignedInteger('ciclo_actual')
                ->default(1);

            $table->timestamps();

            $table->index('fecha_apertura');
            $table->index('responsable_id');
            $table->index('estado_id');
            $table->index('origen_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acciones_correctivas');
    }
};