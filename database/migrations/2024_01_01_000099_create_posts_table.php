<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('content');
            $table->string('media_type')->nullable(); // 'image', 'video', 'audio', null
            $table->string('media_url')->nullable();
            $table->string('share_token', 32)->unique()->nullable();
            $table->integer('likes_count')->default(0);
            $table->integer('reactions_count')->default(0);
            $table->integer('shares_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->integer('views_count')->default(0);
            $table->json('reaction_summary')->nullable(); // {"like": 10, "love": 5, ...}
            $table->boolean('is_pinned')->default(false);
            $table->enum('visibility', ['public', 'friends', 'private'])->default('public');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'created_at']);
            $table->index('visibility');
            $table->index('is_pinned');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
