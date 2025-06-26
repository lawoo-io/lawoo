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
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id('id');
            $table->string('name', 100);
            $table->json('data');
            $table->foreignId('user_id')->constrained()->nullOnDelete()->index();
            $table->string('key', 256)->index();
            $table->boolean('default')->default('0');
            $table->boolean('public')->default('0')->index();
            $table->boolean('is_active')->default('1')->index();
            $table->integer('sequence')->default('10');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
