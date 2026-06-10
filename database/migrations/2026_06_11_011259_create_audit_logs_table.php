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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->comment('Aktor yang melakukan aksi');
            $table->string('event')->comment('Aksi: created, updated, deleted, read');
            $table->string('auditable_type')->comment('Nama Model (Tabel)');
            $table->unsignedBigInteger('auditable_id')->comment('ID dari record yang dimodifikasi');
            $table->text('old_values')->nullable()->comment('Data sebelum diubah (JSON)');
            $table->text('new_values')->nullable()->comment('Data setelah diubah (JSON)');
            $table->string('url')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
