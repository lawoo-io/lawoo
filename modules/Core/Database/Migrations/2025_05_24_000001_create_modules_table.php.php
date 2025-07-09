<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Models\ModuleCategory;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('module_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->nullable();
            $table->string('slug', 150)->unique()->nullable();
            $table->boolean('is_active')->default(1);
        });

        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('system_name', 100)->unique()->index();
            $table->string('short_desc')->nullable();
            $table->foreignIdFor(ModuleCategory::class)->nullable()->constrained()->nullOnDelete();
            $table->string('author', 150);
            $table->string('author_url');
            $table->string('version', 20);
            $table->string('version_installed', 20)->nullable();
            $table->boolean('enabled')->default(0);
            $table->timestamps();
        });

        Schema::create('module_dependencies', function (Blueprint $table) {
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->foreignId('depends_on_id')->constrained('modules')->cascadeOnDelete();
            $table->primary(['module_id', 'depends_on_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_dependencies');
        Schema::dropIfExists('modules');
        Schema::dropIfExists('module_categories');
    }
};
