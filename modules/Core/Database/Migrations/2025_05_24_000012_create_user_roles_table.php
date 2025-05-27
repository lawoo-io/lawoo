<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('expires_at')->nullable(); // Für temporäre Rollenzuweisungen
            $table->timestamps();

            // Unique constraint - verhindert doppelte Zuweisungen
            $table->unique(['user_id', 'role_id']);

            // Indices
            $table->index(['user_id']);
            $table->index(['role_id']);
            $table->index(['expires_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_roles');
    }
};
