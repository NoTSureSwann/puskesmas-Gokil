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
        Schema::create('ai_datasets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kunjungan_id')->nullable()->constrained('kunjungan')->onDelete('cascade');
            $table->text('keluhan');
            $table->json('kemungkinan_penyakit');
            $table->string('tingkat_urgensi');
            $table->string('rekomendasi_poli_nama');
            $table->text('saran_tindakan');
            $table->boolean('is_printed')->default(false);
            $table->timestamp('dicetak_pada')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_datasets');
    }
};
