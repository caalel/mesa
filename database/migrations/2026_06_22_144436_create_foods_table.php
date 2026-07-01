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
        Schema::create('foods', function (Blueprint $table) {
            $table->id();
            $table->string('name_pt');
            $table->string('name_en')->nullable();
            $table->decimal('calories_per_100g', 8, 2);
            $table->decimal('protein_per_100g', 8, 2);
            $table->decimal('carbs_per_100g', 8, 2);
            $table->decimal('fat_per_100g', 8, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('foods');
    }
};
