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
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kunjungan_id')->constrained('kunjungan')->cascadeOnDelete();
            $table->string('kode_pembayaran', 32)->unique();
            $table->decimal('biaya_konsultasi', 12, 2)->default(0.00);
            $table->decimal('biaya_obat', 12, 2)->default(0.00);
            $table->decimal('total_bayar', 12, 2)->storedAs('biaya_konsultasi + biaya_obat');
            $table->enum('metode_pembayaran', ['transfer_bank', 'e_wallet', 'kasir']);
            $table->string('provider_pembayaran')->nullable(); // e.g. 'BCA', 'Mandiri', 'GOPAY', 'OVO'
            $table->enum('status_pembayaran', ['pending', 'paid', 'expired', 'failed'])->default('pending');
            $table->timestamp('waktu_pembayaran')->nullable();
            $table->timestamps();

            $table->index(['kunjungan_id', 'status_pembayaran']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
