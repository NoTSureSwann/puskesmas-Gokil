<?php

declare(strict_types=1);

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
        Schema::table('kunjungan', function (Blueprint $table) {
            $table->foreignId('jadwal_dokter_id')->nullable()->constrained('jadwal_dokter')->after('dokter_id');
            $table->time('jam_kunjungan')->nullable()->after('tanggal_kunjungan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kunjungan', function (Blueprint $table) {
            $table->dropForeign(['jadwal_dokter_id']);
            $table->dropColumn(['jadwal_dokter_id', 'jam_kunjungan']);
        });
    }
};
