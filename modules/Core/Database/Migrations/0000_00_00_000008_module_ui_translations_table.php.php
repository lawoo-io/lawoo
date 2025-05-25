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
        Schema::create('module_ui_translations', function (Blueprint $table) {
            $table->id();
            $table->text('key_string');
            $table->string('locale', 7)->index();
            $table->text('translated_value')->nullable();
            $table->string('module')->nullable();
            $table->boolean('is_auto_created')->default(true);
            $table->boolean('translated_with_ai')->default(false);
            $table->boolean('removed')->default(false);
            $table->timestamps();
            $table->unique(['key_string', 'locale', 'module'], 'unique_module_ui_translation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_ui_translations');
    }
};
