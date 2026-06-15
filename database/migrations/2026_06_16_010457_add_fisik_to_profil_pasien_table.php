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
            $table->integer('tinggi_badan')->nullable()->comment('Dalam cm');
            $table->integer('berat_badan')->nullable()->comment('Dalam kg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profil_pasien', function (Blueprint $table) {
            $table->dropColumn(['tinggi_badan', 'berat_badan']);
        });
    }
};
