<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kos', function (Blueprint $table) {
            $table->text('catatan_verifikasi')->nullable()->after('status_verifikasi');
            $table->timestamp('diverifikasi_pada')->nullable()->after('catatan_verifikasi');
        });
    }

    public function down(): void
    {
        Schema::table('kos', function (Blueprint $table) {
            $table->dropColumn(['catatan_verifikasi', 'diverifikasi_pada']);
        });
    }
};