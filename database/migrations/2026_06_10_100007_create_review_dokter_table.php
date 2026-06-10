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
        Schema::create('review_dokter', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kunjungan_id')->unique()->constrained('kunjungan')->cascadeOnDelete();
            $table->foreignId('pasien_id')->constrained('profil_pasien')->cascadeOnDelete();
            $table->foreignId('dokter_id')->constrained('profil_dokter')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('ulasan')->nullable();
            $table->timestamps();

            $table->index(['dokter_id', 'rating']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_dokter');
    }
};
