<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kos', function (Blueprint $table) {
            $table->string('ukuran_kamar', 30)->nullable()->after('harga');
            $table->unsignedSmallInteger('jumlah_kamar')->nullable()->after('ukuran_kamar');
            $table->unsignedSmallInteger('kamar_tersedia')->nullable()->after('jumlah_kamar');
            $table->string('listrik', 20)->nullable()->after('kamar_tersedia');
            $table->text('peraturan')->nullable()->after('deskripsi');
        });

        Schema::table('foto_kos', function (Blueprint $table) {
            $table->string('kategori', 10)->default('luar')->after('path');
            $table->string('keterangan', 100)->nullable()->after('kategori');
        });
    }

    public function down(): void
    {
        Schema::table('kos', function (Blueprint $table) {
            $table->dropColumn(['ukuran_kamar', 'jumlah_kamar', 'kamar_tersedia', 'listrik', 'peraturan']);
        });

        Schema::table('foto_kos', function (Blueprint $table) {
            $table->dropColumn(['kategori', 'keterangan']);
        });
    }
};