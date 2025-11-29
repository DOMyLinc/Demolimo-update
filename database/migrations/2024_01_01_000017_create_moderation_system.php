<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add moderation columns to existing tables
        Schema::table('tracks', function (Blueprint $table) {
            $table->string('moderation_status')->default('pending')->after('tags'); // pending, approved, rejected
            $table->text('moderation_notes')->nullable()->after('moderation_status');
            $table->timestamp('moderated_at')->nullable()->after('moderation_notes');
            $table->foreignId('moderated_by')->nullable()->constrained('users')->after('moderated_at');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->string('moderation_status')->default('pending')->after('settings');
            $table->text('moderation_notes')->nullable()->after('moderation_status');
            $table->timestamp('moderated_at')->nullable()->after('moderation_notes');
            $table->foreignId('moderated_by')->nullable()->constrained('users')->after('moderated_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_fake')->default(false)->after('is_banned');
            $table->text('bio')->nullable()->after('cover');
        });

        // Auto-Moderation Settings
        Schema::create('auto_moderation_rules', function (Blueprint $table) {
            $table->id();
            $table->string('content_type'); // track, event, user, comment
            $table->string('rule_type'); // auto_approve, auto_reject, keyword_filter, spam_detection
            $table->json('conditions'); // Rule conditions
            $table->string('action'); // approve, reject, flag, delete
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(10);
            $table->timestamps();
        });

        // Moderation Queue
        Schema::create('moderation_queue', function (Blueprint $table) {
            $table->id();
            $table->string('content_type'); // track, event, user, comment
            $table->unsignedBigInteger('content_id');
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->string('reason')->nullable();
            $table->json('flags')->nullable(); // Auto-detected issues
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['content_type', 'content_id']);
        });

        // Reported Content
        Schema::create('content_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('content_type'); // track, event, user, comment
            $table->unsignedBigInteger('content_id');
            $table->string('reason'); // spam, inappropriate, copyright, other
            $table->text('description')->nullable();
            $table->string('status')->default('pending'); // pending, resolved, dismissed
            $table->foreignId('resolved_by')->nullable()->constrained('users');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['content_type', 'content_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_reports');
        Schema::dropIfExists('moderation_queue');
        Schema::dropIfExists('auto_moderation_rules');

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['moderation_status', 'moderation_notes', 'moderated_at', 'moderated_by']);
        });

        Schema::table('tracks', function (Blueprint $table) {
            $table->dropColumn(['moderation_status', 'moderation_notes', 'moderated_at', 'moderated_by']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_fake', 'bio']);
        });
    }
};
