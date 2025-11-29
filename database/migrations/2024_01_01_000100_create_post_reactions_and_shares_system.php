<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Post Reactions (Facebook-style emoji reactions)
        Schema::create('post_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('reaction_type', ['like', 'love', 'haha', 'wow', 'sad', 'angry'])->default('like');
            $table->timestamps();

            $table->unique(['post_id', 'user_id']); // One reaction per user per post
            $table->index(['post_id', 'reaction_type']);
        });

        // Comment Reactions
        Schema::create('comment_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained('comments')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('reaction_type', ['like', 'love', 'haha', 'wow', 'sad', 'angry'])->default('like');
            $table->timestamps();

            $table->unique(['comment_id', 'user_id']); // One reaction per user per comment
            $table->index(['comment_id', 'reaction_type']);
        });

        // Update comments table for nested replies
        Schema::table('comments', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('post_id')->constrained('comments')->onDelete('cascade');
            $table->integer('replies_count')->default(0)->after('content');
            $table->integer('reactions_count')->default(0)->after('replies_count');
        });

        // Update posts table for reactions and shares
        Schema::table('posts', function (Blueprint $table) {
            $table->string('share_token', 32)->unique()->nullable()->after('id');
            $table->integer('reactions_count')->default(0)->after('likes_count');
            $table->integer('shares_count')->default(0)->after('reactions_count');
            $table->json('reaction_summary')->nullable()->after('reactions_count'); // {"like": 10, "love": 5, ...}
        });

        // Post Shares
        Schema::create('post_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('share_platform')->nullable(); // 'internal', 'facebook', 'twitter', 'whatsapp', etc.
            $table->text('share_message')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['post_id', 'user_id']);
            $table->index('created_at');
        });

        // Post Views/Analytics
        Schema::create('post_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referer')->nullable();
            $table->timestamp('viewed_at');

            $table->index(['post_id', 'viewed_at']);
            $table->index('user_id');
        });

        // Reaction Notifications
        Schema::create('reaction_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Recipient
            $table->foreignId('reactor_id')->constrained('users')->onDelete('cascade'); // Who reacted
            $table->string('reactable_type'); // Post or Comment
            $table->unsignedBigInteger('reactable_id');
            $table->enum('reaction_type', ['like', 'love', 'haha', 'wow', 'sad', 'angry']);
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
            $table->index(['reactable_type', 'reactable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reaction_notifications');
        Schema::dropIfExists('post_views');
        Schema::dropIfExists('post_shares');

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['share_token', 'reactions_count', 'shares_count', 'reaction_summary']);
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'replies_count', 'reactions_count']);
        });

        Schema::dropIfExists('comment_reactions');
        Schema::dropIfExists('post_reactions');
    }
};
