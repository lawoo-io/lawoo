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
        Schema::create('yaml_files', function (Blueprint $table) {
            $table->id();
            $table->string('path')->unique();
            $table->timestamp('file_modified_at');
            $table->string('file_hash', 64);
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('yaml_file_db_model', function (Blueprint $table) {
            $table->foreignId('yaml_file_id')->constrained()->cascadeOnDelete();
            $table->foreignId('db_model_id')->constrained()->cascadeOnDelete();
            $table->unique(['yaml_file_id', 'db_model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('yaml_files');
        Schema::dropIfExists('yaml_file_db_model');
    }
};
