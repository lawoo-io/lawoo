<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "Sales Manager"
            $table->string('slug')->unique(); // "sales-manager"
            $table->text('description')->nullable();
            $table->string('module')->default('core'); // Welches Modul diese Rolle definiert
            $table->boolean('is_system')->default(false); // Systemrollen können nicht gelöscht werden
            $table->integer('sort_order')->default(0); // Für UI-Sortierung
            $table->timestamps();
            $table->softDeletes();

            // Indices
            $table->index(['module']);
            $table->index(['slug']);
            $table->index(['is_system']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('roles');
    }
};
