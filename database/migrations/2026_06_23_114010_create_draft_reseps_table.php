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
        Schema::create('draft_reseps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kunjungan_id')->constrained('kunjungan')->onDelete('cascade');
            $table->foreignId('farmasi_id')->nullable()->constrained('profil_farmasi')->onDelete('set null');
            $table->text('catatan_farmasi')->nullable();
            $table->string('status')->default('draft'); // draft, divalidasi, ditolak
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('draft_reseps');
    }
};
