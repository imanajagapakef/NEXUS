<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('membership_id');
            $table->string('action', 100);
            $table->string('entity_type', 50);
            $table->uuid('entity_id');
            $table->timestamp('timestamp');

            $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            $table->foreign(['membership_id', 'organization_id'])
                ->references(['id', 'organization_id'])->on('memberships')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
