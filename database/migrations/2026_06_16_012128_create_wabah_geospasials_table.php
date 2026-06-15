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
        Schema::create('wabah_geospasials', function (Blueprint $table) {
            $table->id();
            $table->string('nama_penyakit');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('kota');
            $table->integer('radius_km');
            $table->enum('tingkat_bahaya', ['Rendah', 'Sedang', 'Tinggi']);
            $table->integer('kasus_aktif');
            $table->text('rekomendasi_ai')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wabah_geospasials');
    }
};
