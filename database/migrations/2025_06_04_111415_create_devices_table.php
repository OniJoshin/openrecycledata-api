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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('phone');
            $table->string('brand')->nullable();
            $table->string('model');
            $table->string('storage')->nullable();
            $table->string('normalized_name');
            $table->string('slug')->unique();
            $table->string('source_slug')->nullable()->unique();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
