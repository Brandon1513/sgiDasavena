<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consecutivos_acciones_correctivas', function (Blueprint $table) {
            $table->id();

            /*
             * Año completo:
             * 2026
             * 2027
             * etc.
             */
            $table->unsignedSmallInteger('anio')->unique();

            /*
             * Último folio utilizado durante ese año.
             *
             * Ejemplo:
             * anio = 2026
             * ultimo_folio = 7
             *
             * Próxima AC = 26-008
             */
            $table->unsignedInteger('ultimo_folio')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consecutivos_acciones_correctivas');
    }
};