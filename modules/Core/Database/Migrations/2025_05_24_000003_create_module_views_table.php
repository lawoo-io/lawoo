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
        Schema::create('module_views', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('override_name')->nullable();
            $table->boolean('base')->default(false);

            $table->foreignIdFor(\Modules\Core\Models\Module::class)
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->string('path');

            $table->string('resource_path')->nullable();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('module_views')
                ->cascadeOnDelete();

            $table->smallInteger('priority')->default(0);

            $table->timestamp('file_modified_at')->nullable();

            $table->string('file_hash', 64)->nullable();

            $table->boolean('content_changed')->default(true);

            $table->timestamps();

            $table->unique(['name', 'module_id'], 'name_module_unique');
            $table->unique(['module_id', 'parent_id'], 'module_parent_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_views');
    }
};
