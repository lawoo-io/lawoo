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
            $table->boolean('isActive')->default('0');
            $table->text('summary')->nullable();
            $table->text('description')->nullable();
            $table->boolean('test1')->default('1');
            $table->boolean('test2')->default('0');
            $table->string('test3')->nullable();
            $table->boolean('test4')->default('0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
