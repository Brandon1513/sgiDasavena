<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ac_ideas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ac_analisis_id')
                ->constrained('ac_analisis')
                ->cascadeOnDelete();

            $table->text('descripcion');

            /*
             * Categorías del diagrama de Ishikawa.
             *
             * mano_obra
             * metodo
             * maquinaria
             * materia_prima
             * medicion
             * medio_ambiente
             */
            $table->string('categoria_ishikawa', 30);

            /*
             * Indica si el equipo considera esta idea
             * como una causa potencial que debe analizarse
             * posteriormente con los 5 Porqués.
             */
            $table->boolean('es_causa_probable')->default(false);

            $table->text('observaciones')->nullable();

            $table->timestamps();

            $table->index('ac_analisis_id');
            $table->index('categoria_ishikawa');
            $table->index('es_causa_probable');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ac_ideas');
    }
};  