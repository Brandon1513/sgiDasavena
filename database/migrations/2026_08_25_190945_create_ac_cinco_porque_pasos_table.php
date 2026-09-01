<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ac_cinco_porques_pasos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ac_cinco_porque_id')
                ->constrained('ac_cinco_porques')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('numero');

            $table->text('pregunta');

            $table->text('respuesta');

            $table->timestamps();

            $table->unique([
                'ac_cinco_porque_id',
                'numero',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ac_cinco_porques_pasos');
    }
};