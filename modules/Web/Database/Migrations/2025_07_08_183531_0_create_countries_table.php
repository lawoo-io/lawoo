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
        Schema::create('countries', function (Blueprint $table) {
            $table->id('id');
            $table->string('name', 100)->unique()->index();
            $table->boolean('is_active')->default('1');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
