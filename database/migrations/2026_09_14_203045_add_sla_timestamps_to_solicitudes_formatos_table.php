<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_formatos', function (Blueprint $table) {
            $table->timestamp('aprobado_jefe_at')->nullable()->after('estado');
            $table->timestamp('atendido_at')->nullable()->after('aprobado_jefe_at');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_formatos', function (Blueprint $table) {
            $table->dropColumn(['aprobado_jefe_at', 'atendido_at']);
        });
    }
};
