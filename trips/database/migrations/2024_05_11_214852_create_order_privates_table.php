<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Driver;
use App\Models\Private_trip;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_privates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Driver::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(Private_trip::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('status')->default('pending');
            $table->decimal('price', 10, 2); // changed to decimal type for precise monetary values


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_privates');
    }
};
