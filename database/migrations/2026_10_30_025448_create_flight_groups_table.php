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
        Schema::create('flight_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('airline')->nullable();
            $table->string('flight_number')->nullable();
            $table->dateTime('departs_at');
            $table->dateTime('returns_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_groups');
    }
};
