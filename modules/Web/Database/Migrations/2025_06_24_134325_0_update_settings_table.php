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
        Schema::table('settings', function (Blueprint $table) {
            $table->dropForeign(['settings_menu_id']);
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->foreign('settings_menu_id')
                ->references('id')
                ->on('settings_menus')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropForeign(['settings_menu_id']);
        });
    }
};
