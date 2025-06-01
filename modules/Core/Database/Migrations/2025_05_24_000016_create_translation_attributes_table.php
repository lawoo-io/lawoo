<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translation_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 5)->index();
            $table->string('model_type')->index();
            $table->unsignedBigInteger('model_id')->index();
            $table->string('attribute_name')->index();
            $table->json('attribute_data');
            $table->timestamps();

            // Unique constraint
            $table->unique(['locale', 'model_type', 'model_id', 'attribute_name'], 'translate_unique');

            // Composite indexes
            $table->index(['model_type', 'model_id']);
            $table->index(['locale', 'model_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translation_attributes');
    }
};
