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
        Schema::table('ai_datasets', function (Blueprint $table) {
            $table->boolean('needs_annotation')->default(false)->after('rekomendasi_poli_nama');
            $table->boolean('is_synthetic')->default(false)->after('needs_annotation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_datasets', function (Blueprint $table) {
            $table->dropColumn(['needs_annotation', 'is_synthetic']);
        });
    }
};
