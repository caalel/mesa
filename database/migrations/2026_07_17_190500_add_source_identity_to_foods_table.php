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
        Schema::table('foods', function (Blueprint $table) {
            $table->string('data_source');
            $table->string('source_code');
            $table->string('source_version');
            $table->unique([
                'data_source',
                'source_code',
                'source_version',
            ], 'foods_source_identity_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('foods', function (Blueprint $table) {
            $table->dropUnique('foods_source_identity_unique');
            $table->dropColumn([
                'data_source',
                'source_code',
                'source_version',
            ]);
        });
    }
};
