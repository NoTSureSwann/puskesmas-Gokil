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
            if (!Schema::hasColumn('ai_datasets', 'model_version')) {
                $table->string('model_version')->default('v1')->after('is_synthetic');
            }
            if (!Schema::hasColumn('ai_datasets', 'nlp_confidence_score')) {
                $table->float('nlp_confidence_score')->default(0.0)->after('model_version');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_datasets', function (Blueprint $table) {
            $table->dropColumn(['model_version', 'nlp_confidence_score']);
        });
    }
};
