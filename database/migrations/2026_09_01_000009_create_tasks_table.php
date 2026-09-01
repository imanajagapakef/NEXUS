<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('project_id');
            $table->uuid('organization_id');
            $table->uuid('assignee_membership_id')->nullable();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->enum('status', ['open', 'in_progress', 'done', 'cancelled']);
            $table->enum('priority', ['low', 'medium', 'high']);
            $table->date('due_date')->nullable();
            $table->timestamps();

            $table->foreign(['project_id', 'organization_id'])
                ->references(['id', 'organization_id'])->on('projects')->cascadeOnDelete();
            $table->foreign(['assignee_membership_id', 'organization_id'])
                ->references(['id', 'organization_id'])->on('memberships')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
