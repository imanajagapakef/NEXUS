<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memberships', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('organization_id');
            $table->uuid('role_id');
            $table->timestamp('joined_at');
            $table->enum('status', ['invited', 'active', 'inactive']);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->restrictOnDelete();

            $table->unique(['user_id', 'organization_id']);
            $table->unique(['id', 'organization_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
