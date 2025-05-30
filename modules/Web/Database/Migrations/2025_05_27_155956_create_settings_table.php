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
        Schema::create('settings', function (Blueprint $table) {
            $table->id('id');
            $table->string('name', 100)->unique();
            $table->string('value')->nullable();
            $table->string('group');
            $table->boolean('isActive')->default('1');
            $table->foreignId('module_id')->constrained()->nullOnDelete();
            $table->integer('sequence')->default('10');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
