<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('documento_versiones', function (Blueprint $table) {
            // publicado_en (lo que te está tronando)
            $table->timestamp('publicado_en')->nullable()->after('publicado_por');

            // SharePoint (te van a servir sí o sí)
            $table->string('sp_drive_id', 100)->nullable()->after('publicado_en');
            $table->string('sp_item_id', 120)->nullable()->after('sp_drive_id');
            $table->text('sp_web_url')->nullable()->after('sp_item_id');
            $table->string('sp_folder_path', 255)->nullable()->after('sp_web_url');
        });
    }

    public function down(): void
    {
        Schema::table('documento_versiones', function (Blueprint $table) {
            $table->dropColumn([
                'publicado_en',
                'sp_drive_id',
                'sp_item_id',
                'sp_web_url',
                'sp_folder_path',
            ]);
        });
    }
};
