<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estados_acciones_correctivas', function (Blueprint $table) {
            $table->id();

            $table->string('codigo', 50)->unique();

            $table->string('nombre', 100);

            $table->unsignedInteger('orden')->default(0);

            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estados_acciones_correctivas');
    }
};