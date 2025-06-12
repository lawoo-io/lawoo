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
            $table->dropColumn('module');
            $table->string('group');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('module');
            $table->dropColumn('group');
        });
    }
};
