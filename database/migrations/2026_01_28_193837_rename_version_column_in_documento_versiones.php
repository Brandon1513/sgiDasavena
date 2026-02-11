<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('documento_versiones', function (Blueprint $table) {
            // Si existe "versión" y no existe "version", renombra
            if (Schema::hasColumn('documento_versiones', 'versión') && !Schema::hasColumn('documento_versiones', 'version')) {
                $table->renameColumn('versión', 'version');
            }
        });
    }

    public function down(): void
    {
        Schema::table('documento_versiones', function (Blueprint $table) {
            if (Schema::hasColumn('documento_versiones', 'version') && !Schema::hasColumn('documento_versiones', 'versión')) {
                $table->renameColumn('version', 'versión');
            }
        });
    }
};
