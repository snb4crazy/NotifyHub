<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('ingest_key', 80)->unique();
            $table->timestamps();
        });

        Schema::create('project_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 20)->default('viewer');
            $table->boolean('can_view_sensitive')->default(false);
            $table->timestamps();

            $table->unique(['project_id', 'user_id']);
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title', 140);
            $table->text('message');
            $table->string('severity', 20);
            $table->string('application', 120)->nullable();
            $table->json('context')->nullable();
            $table->json('sensitive_context')->nullable();
            $table->string('fingerprint', 255)->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->string('source_ip', 45)->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'created_at']);
            $table->index(['project_id', 'severity']);
            $table->index('fingerprint');
        });

        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('platform', 20)->default('unknown');
            $table->string('fcm_token')->unique();
            $table->boolean('notifications_enabled')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_devices');
        Schema::dropIfExists('events');
        Schema::dropIfExists('project_user');
        Schema::dropIfExists('projects');
    }
};
