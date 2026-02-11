<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('document_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('solicitud_formato_id');

            $table->unsignedBigInteger('user_id'); // admin sgi
            $table->string('accion'); // update_version | update_revision | baja | reactivate | edit_meta
            $table->json('antes')->nullable();
            $table->json('despues')->nullable();
            $table->text('comentario')->nullable();

            $table->timestamps();

            $table->foreign('solicitud_formato_id')->references('id')->on('solicitudes_formatos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_histories');
    }
};
