<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_memberships', function (Blueprint $table) {
            $table->uuid('project_id');
            $table->uuid('organization_id');
            $table->uuid('membership_id');
            $table->timestamps();

            $table->primary(['project_id', 'membership_id', 'organization_id']);

            $table->foreign(['project_id', 'organization_id'])
                ->references(['id', 'organization_id'])->on('projects')->cascadeOnDelete();
            $table->foreign(['membership_id', 'organization_id'])
                ->references(['id', 'organization_id'])->on('memberships')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_memberships');
    }
};
