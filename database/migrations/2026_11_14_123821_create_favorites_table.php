<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->foreignId('id_user')->constrained('users', 'id_user')->cascadeOnDelete();
            $table->foreignId('id_kos')->constrained('kos', 'id_kos')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['id_user', 'id_kos']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};