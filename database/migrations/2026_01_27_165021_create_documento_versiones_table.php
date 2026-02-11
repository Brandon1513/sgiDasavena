<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('documento_versiones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('documento_id')->constrained('documentos')->cascadeOnDelete();

            // Versionado
            $table->string('version', 50);               // ej: 1.0, 2.0  (tu folio_version)
            $table->string('revision_actual', 50);       // ej: R1, R2
            $table->string('revision_anterior', 50)->nullable();

            // Fechas base
            $table->date('fecha_version')->nullable();
            $table->date('fecha_revision')->nullable();

            // Vigencias
            $table->unsignedInteger('vigencia_version_dias')->nullable();
            $table->unsignedInteger('vigencia_revision_dias')->nullable();

            // Vencimientos calculados
            $table->date('fecha_vencimiento_version')->nullable()->index();
            $table->date('fecha_vencimiento_revision')->nullable()->index();

            // Estado de esta versión
            $table->enum('estatus', ['vigente', 'obsoleto'])->default('vigente')->index();

            // Almacenamiento
            $table->string('liga_archivo', 2048)->nullable();        // URL pública o SharePoint URL
            $table->string('archivo_storage', 255)->nullable();      // si guardas en Laravel "public/..."
            $table->string('sharepoint_file_id', 255)->nullable();   // si lo subes con Graph API
            $table->string('sharepoint_path', 255)->nullable();      // ruta destino

            // Auditoría
            $table->foreignId('publicado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->date('fecha_publicacion')->nullable();

            $table->timestamps();

            // Un documento no debe repetir la misma version+revision
            $table->unique(['documento_id', 'version', 'revision_actual']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documento_versiones');
    }
};
