<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('creator_membership_id');
            $table->uuid('reviewer_membership_id')->nullable();
            $table->uuid('approver_membership_id')->nullable();
            $table->text('description');
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->foreign(['creator_membership_id', 'organization_id'])
                ->references(['id', 'organization_id'])->on('memberships')->restrictOnDelete();
            $table->foreign(['reviewer_membership_id', 'organization_id'])
                ->references(['id', 'organization_id'])->on('memberships')->restrictOnDelete();
            $table->foreign(['approver_membership_id', 'organization_id'])
                ->references(['id', 'organization_id'])->on('memberships')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
