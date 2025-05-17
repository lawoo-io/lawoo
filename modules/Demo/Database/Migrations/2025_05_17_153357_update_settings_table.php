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
            $table->string('test_demo_field_one', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('test_demo_field_one');
        });
    }
};
