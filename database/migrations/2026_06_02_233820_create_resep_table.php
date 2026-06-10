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
        Schema::create('resep', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kunjungan_id')->constrained('kunjungan');
            $table->foreignId('dokter_id')->constrained('profil_dokter');
            $table->string('no_resep', 20)->unique();
            $table->text('catatan_dokter')->nullable();
            $table->enum('prioritas', ['normal', 'urgen'])->default('normal');
            $table->enum('status', ['menunggu', 'diproses', 'selesai', 'batal'])->default('menunggu');
            $table->timestamp('jam_input_resep');
            $table->timestamp('jam_selesai_farmasi')->nullable();
            $table->timestamps();

            $table->index(['status', 'jam_input_resep']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resep');
    }
};
