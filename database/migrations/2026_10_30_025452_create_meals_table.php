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
        Schema::create('meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->text('menu')->nullable();
            $table->dateTime('served_at');
            $table->timestamps();

            $table->index(['agency_id', 'served_at']);
        });

        Schema::create('mealables', function (Blueprint $table) {
            $table->foreignId('meal_id')->constrained()->cascadeOnDelete();
            $table->morphs('mealable');
            $table->timestamps();

            $table->primary(['meal_id', 'mealable_type', 'mealable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mealables');
        Schema::dropIfExists('meals');
    }
};
