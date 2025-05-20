<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('solicitudes_formatos', function (Blueprint $table) {
            $table->date('fecha_alta_sgi')->nullable()->after('observaciones_sgi');
        });
    }

    public function down()
    {
        Schema::table('solicitudes_formatos', function (Blueprint $table) {
            $table->dropColumn('fecha_alta_sgi');
        });
    }
};
