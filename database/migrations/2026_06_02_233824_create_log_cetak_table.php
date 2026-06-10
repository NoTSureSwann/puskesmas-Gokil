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
        Schema::connection('sqlite_log')->create('log_cetak', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('resep_id');
            $table->unsignedBigInteger('farmasi_user_id');
            $table->string('no_resep', 20);
            $table->string('nama_pasien');
            $table->string('filename_pdf');
            $table->string('path_pdf');
            $table->timestamp('dicetak_pada');
            $table->boolean('is_reprint')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('sqlite_log')->dropIfExists('log_cetak');
    }
};
