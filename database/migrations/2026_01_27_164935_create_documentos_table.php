<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();

            // Identidad del documento (único en SGI)
            $table->string('codigo', 80)->unique();      // ej: F-DIR-05
            $table->string('nombre', 255);

            // Meta
            $table->string('tipo_documento', 100)->nullable(); // Procedimiento, Formato, Manual...
            $table->string('formato_el_pa', 20)->nullable();   // EL/PA
            $table->string('area', 120)->nullable();           // Comercial, Finanzas...
            $table->string('sharepoint_site', 255)->nullable(); // opcional
            $table->string('sharepoint_folder', 255)->nullable(); // ruta relativa o folder id

            // Estado “global” del documento (no de la versión)
            $table->enum('estatus', ['vigente', 'baja'])->default('vigente');

            // Para saber rápido cuál es la vigente
            $table->foreignId('version_vigente_id')->nullable()->index(); // se apunta a documento_versiones.id

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
