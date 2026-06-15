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
        Schema::table('profil_pasien', function (Blueprint $table) {
            $table->string('tempat_lahir')->nullable()->change();
            $table->text('alamat')->nullable()->change();
            $table->string('kelurahan')->nullable()->change();
            $table->string('kecamatan')->nullable()->change();
            $table->enum('jenis_pasien', ['umum', 'bpjs'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profil_pasien', function (Blueprint $table) {
            //
        });
    }
};
