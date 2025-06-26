<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    /**
    * Create Template
    **/
    public function up(): void
    {
        Schema::create('settings_menus', function (Blueprint $table) {
            $table->id('id');
            $table->string('name', 100)->unique();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default('1');
            $table->smallInteger('sequence')->default('10');
            $table->string('module_name', 100)->nullable();
            $table->boolean('is_base')->default('1');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings_menus');
    }
};
