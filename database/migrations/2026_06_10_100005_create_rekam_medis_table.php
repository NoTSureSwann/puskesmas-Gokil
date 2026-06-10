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
        Schema::create('rekam_medis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kunjungan_id')->unique()->constrained('kunjungan')->cascadeOnDelete();
            $table->foreignId('pasien_id')->constrained('profil_pasien')->cascadeOnDelete();
            $table->foreignId('dokter_id')->constrained('profil_dokter')->cascadeOnDelete();
            $table->text('keluhan');
            $table->text('pemeriksaan_fisik')->nullable();
            $table->text('diagnosa');
            $table->text('tindakan')->nullable();
            $table->text('resep_tambahan_catatan')->nullable();
            $table->timestamps();

            $table->index(['pasien_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekam_medis');
    }
};
