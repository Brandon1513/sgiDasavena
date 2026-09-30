<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('soporte_tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('mesa_ayuda_ticket_id')->nullable()->after('error_envio');
            $table->string('mesa_ayuda_folio')->nullable()->after('mesa_ayuda_ticket_id');
            $table->string('mesa_ayuda_sync_estado')->default('pendiente')->after('mesa_ayuda_folio');
            $table->text('mesa_ayuda_sync_error')->nullable()->after('mesa_ayuda_sync_estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('soporte_tickets', function (Blueprint $table) {
            $table->dropColumn([
                'mesa_ayuda_ticket_id',
                'mesa_ayuda_folio',
                'mesa_ayuda_sync_estado',
                'mesa_ayuda_sync_error',
            ]);
        });
    }
};
