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
        Schema::create('kunjungan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasien_id')->constrained('profil_pasien');
            $table->foreignId('poli_id')->constrained('poli');
            $table->foreignId('dokter_id')->nullable()->constrained('profil_dokter');
            $table->foreignId('loket_user_id')->nullable()->constrained('users');
            $table->string('no_kunjungan', 20)->unique();
            $table->integer('no_antrian');
            $table->date('tanggal_kunjungan');
            $table->text('keluhan')->nullable();
            $table->enum('status', [
                'menunggu', 'dipanggil', 'diperiksa', 'resep', 'selesai', 'batal'
            ])->default('menunggu');
            $table->enum('jenis_kunjungan', ['umum', 'bpjs'])->default('umum');
            $table->timestamp('jam_daftar');
            $table->timestamp('jam_panggil')->nullable();
            $table->timestamp('jam_selesai')->nullable();
            $table->timestamps();

            $table->index(['tanggal_kunjungan', 'poli_id']);
            $table->index(['pasien_id', 'tanggal_kunjungan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kunjungan');
    }
};
