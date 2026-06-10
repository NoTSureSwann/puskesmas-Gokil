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
        Schema::create('monitor_kesehatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasien_id')->constrained('profil_pasien')->cascadeOnDelete();
            $table->decimal('berat_badan', 5, 2)->nullable();
            $table->decimal('tinggi_badan', 5, 2)->nullable();
            $table->integer('tensi_sistolik')->nullable();
            $table->integer('tensi_diastolik')->nullable();
            $table->integer('detak_jantung')->nullable();
            $table->decimal('suhu_tubuh', 4, 2)->nullable();
            $table->date('tanggal_pencatatan');
            $table->text('catatan_pasien')->nullable();
            $table->timestamps();

            $table->index(['pasien_id', 'tanggal_pencatatan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitor_kesehatan');
    }
};
