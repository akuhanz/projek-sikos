<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('foto_kos', function (Blueprint $table) {
            $table->id('id_foto');

            $table->foreignId('id_kos')
                ->constrained('kos', 'id_kos')
                ->cascadeOnDelete();

            $table->string('path');
            $table->unsignedTinyInteger('urutan')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foto_kos');
    }
};