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
        Schema::create('patient_consents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('ID Pasien');
            $table->string('consent_type')->comment('Jenis Persetujuan (misal: AI_PROCESSING, DATA_SHARING)');
            $table->boolean('is_granted')->default(false);
            $table->text('consent_text')->nullable()->comment('Teks persetujuan yang disetujui');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('revoked_at')->nullable()->comment('Kapan persetujuan dicabut');
            $table->timestamps();

            // Foreign key asumsikan ada tabel users
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_consents');
    }
};
