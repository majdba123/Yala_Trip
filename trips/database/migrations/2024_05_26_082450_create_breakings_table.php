<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Path;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('breakings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Path::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer('sorted');
            $table->string('name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('breakings');
    }
};
