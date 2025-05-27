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
        Schema::create('db_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('new')->default(true);
            $table->boolean('changed')->default(false);
            $table->boolean('migrated')->default(false);
            $table->boolean('removed')->default(false);
            $table->timestamps();
        });

        Schema::create('db_model_module', function (Blueprint $table) {
            $table->foreignId('db_model_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->primary(['db_model_id', 'module_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('db_model_module');
        Schema::dropIfExists('db_models');
    }
};
