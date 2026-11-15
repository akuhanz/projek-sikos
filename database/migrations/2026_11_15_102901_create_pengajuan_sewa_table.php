<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_sewa', function (Blueprint $table) {
            $table->id('id_pengajuan');

            $table->foreignId('id_kos')->constrained('kos', 'id_kos')->cascadeOnDelete();
            $table->foreignId('id_user')->constrained('users', 'id_user')->cascadeOnDelete(); // pencari

            $table->date('tanggal_masuk');
            $table->unsignedTinyInteger('durasi_bulan');
            $table->unsignedTinyInteger('jumlah_penghuni')->default(1);
            $table->text('catatan')->nullable();

            $table->string('status', 20)->default('menunggu'); // menunggu, diterima, ditolak, dibatalkan
            $table->text('catatan_pemilik')->nullable();
            $table->timestamp('direspons_pada')->nullable();

            $table->timestamps();

            $table->index(['id_kos', 'status']);
            $table->index(['id_user', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_sewa');
    }
};