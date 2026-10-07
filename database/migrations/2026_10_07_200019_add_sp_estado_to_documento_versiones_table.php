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
        Schema::table('documento_versiones', function (Blueprint $table) {
            $table->string('sp_estado', 20)->nullable()->after('sp_folder_path');
            $table->text('sp_error')->nullable()->after('sp_estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documento_versiones', function (Blueprint $table) {
            $table->dropColumn(['sp_estado', 'sp_error']);
        });
    }
};
