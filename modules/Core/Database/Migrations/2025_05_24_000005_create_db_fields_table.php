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
        Schema::create('db_fields', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('description')->nullable();
            $table->string('hint')->nullable();
            $table->boolean('created')->default(false);
            $table->boolean('changed')->default(true);
            $table->boolean('migrated')->default(false);
            $table->boolean('to_remove')->default(false);
            $table->foreignId('db_model_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_id')->nullable()->constrained()->nullOnDelete();
            $table->string('params')->nullable();
            $table->string('new_params')->nullable();
            $table->integer('sequence')->default(10);
            $table->string('group', 100)->nullable();
            $table->string('css_class', 100)->nullable();
            $table->timestamps();
            $table->unique(['db_model_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('db_fields');
    }
};
