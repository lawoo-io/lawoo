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
            $table->string('value')->nullable();
            $table->foreignId('settings_menu_id')->constrained()->onDelete('cascade');
            $table->string('key')->unique()->index();
            $table->string('module_name', 100)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
