<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_sewa', function (Blueprint $table) {
            $table->timestamp('diakhiri_pada')->nullable()->after('direspons_pada');
            $table->string('diakhiri_oleh', 10)->nullable()->after('diakhiri_pada'); // pencari atau pemilik
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_sewa', function (Blueprint $table) {
            $table->dropColumn(['diakhiri_pada', 'diakhiri_oleh']);
        });
    }
};