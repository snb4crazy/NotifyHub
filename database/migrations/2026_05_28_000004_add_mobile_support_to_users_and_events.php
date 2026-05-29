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
        Schema::table('users', function (Blueprint $table) {
            $table->string('timezone', 64)->nullable()->after('email_verified_at');
            $table->json('notification_preferences')->nullable()->after('remember_token');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->string('event_type', 80)->default('generic')->after('project_id');
            $table->string('environment', 50)->nullable()->after('application');
            $table->index(['project_id', 'event_type']);
            $table->index(['project_id', 'environment']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'event_type']);
            $table->dropIndex(['project_id', 'environment']);
            $table->dropColumn(['event_type', 'environment']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['timezone', 'notification_preferences']);
        });
    }
};

