<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "Create Invoice"
            $table->string('slug')->unique(); // "sales.create_invoice"
            $table->string('module'); // "core", "sales", "inventory"
            $table->string('resource')->nullable(); // "invoice", "customer"
            $table->string('action')->nullable(); // "create", "view", "edit", "delete"
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            // Indices für bessere Performance
            $table->index(['module']);
            $table->index(['resource', 'action']);
            $table->index(['slug']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('permissions');
    }
};
