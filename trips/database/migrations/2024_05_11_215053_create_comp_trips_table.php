<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Company;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comp_trips', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Company::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('from');
            $table->string('to');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('price', 10, 2); // changed to decimal type for precise monetary values
            $table->string('status')->default('pending');
            $table->string('type')->default('0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comp_trips');
    }
};
