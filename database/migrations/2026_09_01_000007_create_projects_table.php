<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'completed', 'archived']);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->unique(['organization_id', 'name']);
            $table->unique(['id', 'organization_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
