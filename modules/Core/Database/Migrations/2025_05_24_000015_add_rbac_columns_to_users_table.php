<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_super_admin')->default(false); // KEIN after()
            $table->boolean('is_active')->default(true); // KEIN after()
            $table->timestamp('last_permission_check')->nullable(); // KEIN after()
            $table->foreignId('language_id')->nullable()->constrained('languages')->nullOnDelete();

            // Index für Performance
            $table->index(['is_super_admin']);
            $table->index(['is_active']);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['language_id']);
            // Indices ZUERST löschen, dann Spalten
            $table->dropIndex(['is_super_admin']);
            $table->dropIndex(['is_active']);

            // Dann Spalten löschen
            $table->dropColumn([
                'is_super_admin',
                'is_active',
                'last_permission_check',
                'language_id'
            ]);
        });
    }
};
