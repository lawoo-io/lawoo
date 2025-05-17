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
        Schema::create('migration_files', function (Blueprint $table) {
            $table->id();
            $table->string('path')->unique();
            $table->boolean('migrated')->default(false);
            $table->boolean('rollback')->default(false);
            $table->boolean('reset')->default(false);
            $table->foreignId('db_model_id')->constrained()->cascadeOnDelete();
            $table->foreignId('migration_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('migration_files');
    }
};
