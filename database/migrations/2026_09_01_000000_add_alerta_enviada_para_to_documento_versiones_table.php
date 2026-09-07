<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('documento_versiones', function (Blueprint $table) {
            // Guarda la fecha_vencimiento_* para la que ya se envió alerta,
            // así el comando documentos:verificar-vencimientos no reenvía el
            // mismo correo cada día mientras el documento siga en la misma zona.
            $table->date('alerta_version_enviada_para')->nullable()->after('fecha_vencimiento_revision');
            $table->date('alerta_revision_enviada_para')->nullable()->after('alerta_version_enviada_para');
        });
    }

    public function down(): void
    {
        Schema::table('documento_versiones', function (Blueprint $table) {
            $table->dropColumn(['alerta_version_enviada_para', 'alerta_revision_enviada_para']);
        });
    }
};