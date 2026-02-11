<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('solicitudes_formatos', function (Blueprint $table) {
            $table->date('fecha_revision')->nullable()->after('fecha_version');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_formatos', function (Blueprint $table) {
            $table->dropColumn('fecha_revision');
        });
    }
};
