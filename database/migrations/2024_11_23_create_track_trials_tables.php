<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add creator status to users
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_creator')->default(false)->after('email');
            $table->string('creator_title')->nullable()->after('is_creator'); // e.g., "Alpha Creator", "Beta Creator"
        });

        // Track Trials Table
        Schema::create('track_trials', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('active'); // active, completed, upcoming
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });

        // Track Trial Entries (Songs uploaded to a trial)
        Schema::create('track_trial_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('track_trial_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The Creator
            $table->string('track_title');
            $table->string('audio_path');
            $table->string('cover_image')->nullable();
            $table->integer('votes')->default(0);
            $table->integer('plays')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('track_trial_entries');
        Schema::dropIfExists('track_trials');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_creator', 'creator_title']);
        });
    }
};
