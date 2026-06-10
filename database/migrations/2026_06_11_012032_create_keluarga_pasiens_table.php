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
        Schema::create('keluarga_pasiens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pasien_id')->comment('ID ProfilPasien (Kepala Keluarga / Pasien Utama)');
            $table->string('nama_lengkap');
            $table->string('hubungan')->comment('Istri, Suami, Anak, Orang Tua');
            $table->string('nik', 16)->nullable()->comment('Enkripsi di level model');
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->timestamps();
            
            // $table->foreign('pasien_id')->references('id')->on('profil_pasien')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keluarga_pasiens');
    }
};
