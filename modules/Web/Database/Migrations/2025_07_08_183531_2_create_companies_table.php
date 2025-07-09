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
        Schema::create('companies', function (Blueprint $table) {
            $table->id('id');
            $table->string('name', 150)->unique()->index();
            $table->string('street')->nullable();
            $table->string('street_2')->nullable();
            $table->string('zip', 20)->nullable();
            $table->string('city')->nullable();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->boolean('is_active')->default('1');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
