<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('documento_revisiones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('documento_version_id')
                ->constrained('documento_versiones')
                ->cascadeOnDelete();

            // Datos de la revisión
            $table->string('revision_actual', 50);
            $table->string('revision_anterior', 50)->nullable();

            $table->date('fecha_revision')->nullable();
            $table->unsignedInteger('vigencia_revision_dias')->nullable();
            $table->date('fecha_vencimiento_revision')->nullable();

            // Archivo/SharePoint de ESA revisión (puede cambiar)
            $table->text('liga_archivo')->nullable();
            $table->string('lugar_almacenamiento', 255)->nullable();

            // control
            $table->enum('estatus', ['vigente','obsoleta'])->default('vigente');
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('registrado_en')->nullable();

            $table->timestamps();

            $table->index(['documento_version_id', 'fecha_vencimiento_revision'], 'drv_ver_venc_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documento_revisiones');
    }
};
