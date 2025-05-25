<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Unique constraint - verhindert doppelte Zuweisungen
            $table->unique(['role_id', 'permission_id']);

            // Indices für bessere Performance
            $table->index(['role_id']);
            $table->index(['permission_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('role_permissions');
    }
};
