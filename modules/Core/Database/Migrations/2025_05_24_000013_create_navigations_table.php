<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('navigations', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->index();
            $table->string('route')->index();
            $table->string('middleware')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('navigations')->onDelete('cascade');
            $table->string('module', 50)->index();
            $table->smallInteger('level')->default(0)->index();
            $table->integer('sort_order')->default(100);
            $table->string('name');
            $table->string('icon')->nullable();
            $table->string('group_name')->nullable();
            $table->integer('group_order')->default(100);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_user_modified')->default(false);
            $table->timestamps();

            // Indices for Performance
            $table->index(['module', 'level', 'sort_order']);
            $table->index(['parent_id', 'sort_order']);
            $table->index(['level', 'group_name', 'group_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('navigations');
    }
};
