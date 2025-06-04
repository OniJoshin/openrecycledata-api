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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('merchant');
            $table->decimal('price', 8, 2);
            $table->string('condition');
            $table->string('network')->nullable(); // e.g., Unlocked, EE, Vodafone
            $table->string('source'); // e.g., Unlocked, EE, Vodafone
            $table->timestamp('timestamp'); // when the offer was scraped
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
