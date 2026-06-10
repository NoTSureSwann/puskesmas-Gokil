<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('profil_dokter', function (Blueprint $table) {
            $table->decimal('harga_konsultasi', 10, 2)->default(50000.00)->after('poli');
            $table->string('jam_kerja')->default('08:00 - 15:00')->after('harga_konsultasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profil_dokter', function (Blueprint $table) {
            $table->dropColumn(['harga_konsultasi', 'jam_kerja']);
        });
    }
};
