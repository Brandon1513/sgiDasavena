<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ac_actividades', function (Blueprint $table) {
            // Mismo mecanismo anti-spam que document_versions.alerta_version_enviada_para:
            // no se reenvía el recordatorio si ya se avisó para esta misma fecha de compromiso.
            $table->date('alerta_vencimiento_enviada_para')->nullable()->after('fecha_cumplimiento');
        });
    }

    public function down(): void
    {
        Schema::table('ac_actividades', function (Blueprint $table) {
            $table->dropColumn('alerta_vencimiento_enviada_para');
        });
    }
};
