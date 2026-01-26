<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('solicitudes_formatos', function (Blueprint $table) {
            $table->string('codigo_documento')->nullable()->index();
            $table->string('tipo_documento')->nullable();
            $table->string('nombre_documento')->nullable();

            $table->string('formato_el_pa')->nullable();
            $table->string('folio_version')->nullable();

            $table->date('fecha_version')->nullable();
            $table->integer('vigencia_version_dias')->default(365);
            $table->integer('vigencia_revision_dias')->default(730);

            $table->date('fecha_vencimiento_version')->nullable()->index();
            $table->date('fecha_vencimiento_revision')->nullable()->index();

            $table->string('lugar_almacenamiento')->nullable();
            $table->text('motivo_baja')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_formatos', function (Blueprint $table) {
            $table->dropColumn([
                'codigo_documento',
                'tipo_documento',
                'nombre_documento',
                'formato_el_pa',
                'folio_version',
                'fecha_version',
                'vigencia_version_dias',
                'vigencia_revision_dias',
                'fecha_vencimiento_version',
                'fecha_vencimiento_revision',
                'lugar_almacenamiento',
                'motivo_baja',
            ]);
        });
    }
};
