<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
    * Change Template
    **/
    public function up(): void
    {
        Schema::table('settings_menus', function (Blueprint $table) {
            $table->dropColumn('is_base');
        });
    }

    public function down(): void
    {
        Schema::table('settings_menus', function (Blueprint $table) {
            $table->boolean('is_base')->default('1');
        });
    }
};
