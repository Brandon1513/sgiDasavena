<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('solicitudes_formatos', function (Blueprint $table) {
            $table->foreignId('documento_id')
                ->nullable()
                ->after('user_id')
                ->constrained('documentos')
                ->nullOnDelete();

            $table->index('documento_id');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_formatos', function (Blueprint $table) {
            $table->dropForeign(['documento_id']);
            $table->dropIndex(['documento_id']);
            $table->dropColumn('documento_id');
        });
    }
};
