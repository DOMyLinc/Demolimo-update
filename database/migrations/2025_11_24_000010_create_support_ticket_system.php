<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Support Topics (configurable from admin panel)
        Schema::create('support_topics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable(); // Icon class or emoji
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_login')->default(false); // Some topics may require login
            $table->timestamps();
        });

        // Support Tickets
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique(); // e.g., TICKET-2024-00001
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Null if guest
            $table->foreignId('topic_id')->constrained('support_topics')->onDelete('cascade');

            // Submitter info (for guests)
            $table->string('name')->nullable();
            $table->string('email')->nullable();

            $table->string('subject');
            $table->text('message');
            $table->string('priority')->default('normal'); // low, normal, high, urgent
            $table->string('status')->default('open'); // open, assigned, in_progress, waiting_user, resolved, closed

            // Assignment
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('assigned_at')->nullable();

            // Tracking
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('last_reply_at')->nullable();

            // Metadata
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('attachments')->nullable();

            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('assigned_to');
            $table->index('topic_id');
        });

        // Support Ticket Replies
        Schema::create('support_ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->text('message');
            $table->boolean('is_staff_reply')->default(false);
            $table->boolean('is_internal_note')->default(false); // Internal notes not visible to user
            $table->json('attachments')->nullable();
            $table->timestamps();

            $table->index(['ticket_id', 'created_at']);
        });

        // Staff Assignments (which staff can handle which topics)
        Schema::create('support_topic_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained('support_topics')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('can_assign')->default(true);
            $table->boolean('auto_assign')->default(false); // Auto-assign new tickets
            $table->timestamps();

            $table->unique(['topic_id', 'user_id']);
        });

        // Canned Responses (quick replies for staff)
        Schema::create('canned_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Null = global
            $table->string('title');
            $table->text('content');
            $table->json('available_for_topics')->nullable(); // Null = all topics
            $table->boolean('is_global')->default(false); // Available to all staff
            $table->integer('usage_count')->default(0);
            $table->timestamps();
        });

        // Ticket Ratings (user satisfaction)
        Schema::create('ticket_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->onDelete('cascade');
            $table->integer('rating'); // 1-5 stars
            $table->text('feedback')->nullable();
            $table->timestamps();

            $table->unique('ticket_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ticket_ratings');
        Schema::dropIfExists('canned_responses');
        Schema::dropIfExists('support_topic_staff');
        Schema::dropIfExists('support_ticket_replies');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('support_topics');
    }
};
