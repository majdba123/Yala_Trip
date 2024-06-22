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
        Schema::create('paths', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('from', 100); // increased length to 100 characters
            $table->string('to', 100); // increased length to 100 characters
            $table->string('city', 50); // assuming 50 characters for the city
            $table->decimal('price', 10, 2); // changed to decimal type for precise monetary values
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paths');
    }
};
