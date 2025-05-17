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
        Schema::create('overrides', function (Blueprint $table) {
            $table->id();
            $table->string('original_class');
            $table->string('override_class');
            $table->foreignIdFor(\Modules\Core\Models\Module::class)->constrained()->cascadeOnDelete();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['original_class', 'override_class']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overrides');
    }
};
