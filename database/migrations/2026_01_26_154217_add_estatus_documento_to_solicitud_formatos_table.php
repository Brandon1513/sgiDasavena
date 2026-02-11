<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('solicitudes_formatos', function (Blueprint $table) {
            $table->string('estatus_documento')->default('vigente'); // vigente|baja|suspendido
            $table->date('fecha_baja')->nullable();
            $table->text('motivo_baja_sgi')->nullable();
            $table->unsignedBigInteger('baja_por')->nullable();

            $table->index('estatus_documento');
        });
    }

    public function down(): void
    {
        Schema::table('solicitud_formatos', function (Blueprint $table) {
            $table->dropIndex(['estatus_documento']);
            $table->dropColumn(['estatus_documento','fecha_baja','motivo_baja_sgi','baja_por']);
        });
    }
};
