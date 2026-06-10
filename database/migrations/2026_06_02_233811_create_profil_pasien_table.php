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
        Schema::create('profil_pasien', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nik', 16)->unique();
            $table->string('no_bpjs', 13)->nullable()->unique();
            $table->string('no_kk', 16)->nullable();
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->date('tanggal_lahir');
            $table->string('tempat_lahir');
            $table->text('alamat');
            $table->string('kelurahan')->nullable();
            $table->string('kecamatan')->nullable();
            $table->enum('jenis_pasien', ['umum', 'bpjs'])->default('umum');
            $table->text('riwayat_alergi')->nullable();
            $table->string('golongan_darah', 3)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('nik');
            $table->index('no_bpjs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profil_pasien');
    }
};
