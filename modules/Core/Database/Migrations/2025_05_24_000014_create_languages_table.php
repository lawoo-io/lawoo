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
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 5)->unique()->comment('Language code (e.g., de, en, fr)');
            $table->string('name', 100)->comment('Display name (e.g., Deutsch, English)');
            $table->boolean('is_active')->default(false)->comment('Is language active in system');
            $table->boolean('is_default')->default(false)->comment('Is default/fallback language');
            $table->timestamps();

            // Indexes for performance
            $table->index(['is_active']);
            $table->index('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
