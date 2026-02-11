<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('documento_versiones', function (Blueprint $table) {

            // Si NO existen, se agregan
            if (!Schema::hasColumn('documento_versiones', 'lugar_almacenamiento')) {
                $table->string('lugar_almacenamiento', 255)->nullable()->after('liga_archivo');
            }

            if (!Schema::hasColumn('documento_versiones', 'publicado_por')) {
                $table->unsignedBigInteger('publicado_por')->nullable()->after('estatus');
            }

            if (!Schema::hasColumn('documento_versiones', 'publicado_en')) {
                $table->timestamp('publicado_en')->nullable()->after('publicado_por');
            }

            // (Opcional SharePoint)
            if (!Schema::hasColumn('documento_versiones', 'sp_drive_id')) {
                $table->string('sp_drive_id', 100)->nullable();
            }
            if (!Schema::hasColumn('documento_versiones', 'sp_item_id')) {
                $table->string('sp_item_id', 100)->nullable();
            }
            if (!Schema::hasColumn('documento_versiones', 'sp_web_url')) {
                $table->text('sp_web_url')->nullable();
            }
            if (!Schema::hasColumn('documento_versiones', 'sp_folder_path')) {
                $table->string('sp_folder_path', 255)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('documento_versiones', function (Blueprint $table) {
            $cols = [
                'lugar_almacenamiento',
                'publicado_por',
                'publicado_en',
                'sp_drive_id',
                'sp_item_id',
                'sp_web_url',
                'sp_folder_path',
            ];

            foreach ($cols as $c) {
                if (Schema::hasColumn('documento_versiones', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
