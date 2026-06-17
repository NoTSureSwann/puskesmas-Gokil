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
        Schema::create('panggilan_ambulans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasien_id')->constrained('profil_pasien')->cascadeOnDelete();
            $table->text('alamat_jemput');
            $table->string('no_telepon');
            $table->text('keluhan_darurat')->nullable();
            $table->enum('status', ['menunggu', 'dijemput', 'selesai', 'batal'])->default('menunggu');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panggilan_ambulans');
    }
};
