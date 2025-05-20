<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_formatos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Usuario que solicita
            $table->enum('accion', ['actualizacion', 'baja', 'nuevo_documento']);
            $table->string('archivo_adjunto')->nullable();

            $table->enum('estado', [
                'pendiente', 'aprobado_jefe', 'rechazado_jefe', 
                'evaluando_sgi', 'atendido', 'rechazado_sgi'
            ])->default('pendiente');

            $table->foreignId('jefe_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('observaciones_jefe')->nullable();

            $table->foreignId('administrador_sgi_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('revision_actual')->nullable();
            $table->string('revision_anterior')->nullable();
            $table->string('liga_archivo')->nullable();
            $table->text('observaciones_sgi')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_formatos');
    }
};
