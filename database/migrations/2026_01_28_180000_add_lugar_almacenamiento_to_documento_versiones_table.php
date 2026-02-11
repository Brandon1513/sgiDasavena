<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('documento_versiones', function (Blueprint $table) {
            $table->string('lugar_almacenamiento', 255)->nullable()->after('liga_archivo');
        });
    }

    public function down(): void
    {
        Schema::table('documento_versiones', function (Blueprint $table) {
            $table->dropColumn('lugar_almacenamiento');
        });
    }
};
