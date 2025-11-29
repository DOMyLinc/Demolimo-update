<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Playlist Collaborators
        Schema::create('playlist_collaborators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('playlist_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('permission', ['view', 'edit', 'admin'])->default('edit');
            $table->foreignId('invited_by')->constrained('users');
            $table->timestamps();
            $table->unique(['playlist_id', 'user_id']);
        });

        // Lyrics
        Schema::create('lyrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('track_id')->constrained()->onDelete('cascade');
            $table->text('content');
            $table->text('synced_lyrics')->nullable(); // LRC format
            $table->string('language', 10)->default('en');
            $table->boolean('is_synced')->default(false);
            $table->timestamps();
        });

        // Content Reports
        Schema::create('content_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->morphs('reportable'); // track, album, user, etc.
            $table->enum('reason', ['spam', 'inappropriate', 'copyright', 'harassment', 'other']);
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'reviewed', 'resolved', 'dismissed'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });

        // Email Campaigns
        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subject');
            $table->text('content');
            $table->enum('status', ['draft', 'scheduled', 'sending', 'sent'])->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->integer('recipients_count')->default(0);
            $table->integer('opened_count')->default(0);
            $table->integer('clicked_count')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('email_campaigns');
        Schema::dropIfExists('content_reports');
        Schema::dropIfExists('lyrics');
        Schema::dropIfExists('playlist_collaborators');
    }
};
