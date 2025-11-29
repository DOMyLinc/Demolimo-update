<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Zipcode Ownership System
        Schema::create('zipcode_owners', function (Blueprint $table) {
            $table->id();
            $table->string('zipcode', 10)->unique();
            $table->string('country_code', 2); // US, UK, etc.
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->timestamp('claimed_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['zipcode', 'country_code']);
            $table->index('owner_id');
        });

        // Zipcode Members
        Schema::create('zipcode_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zipcode_owner_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role')->default('member'); // member, moderator
            $table->boolean('is_approved')->default(true);
            $table->timestamp('joined_at');
            $table->timestamps();

            $table->unique(['zipcode_owner_id', 'user_id']);
            $table->index('user_id');
        });

        // Zipcode Posts/Feed
        Schema::create('zipcode_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zipcode_owner_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('content');
            $table->json('media')->nullable(); // Images, videos
            $table->foreignId('track_id')->nullable()->constrained()->onDelete('set null');
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();

            $table->index(['zipcode_owner_id', 'created_at']);
        });

        // Zipcode Events
        Schema::create('zipcode_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zipcode_owner_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->timestamp('event_date');
            $table->string('location')->nullable();
            $table->integer('max_attendees')->nullable();
            $table->timestamps();
        });

        // Zipcode Settings
        Schema::create('zipcode_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zipcode_owner_id')->constrained()->onDelete('cascade');
            $table->boolean('allow_posts')->default(true);
            $table->boolean('require_approval')->default(false);
            $table->boolean('allow_events')->default(true);
            $table->string('theme_color')->default('#3B82F6');
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->timestamps();
        });

        // Music Studio Projects
        Schema::create('studio_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->integer('bpm')->default(120);
            $table->string('key')->default('C');
            $table->string('time_signature')->default('4/4');
            $table->json('project_data'); // Complete project state
            $table->string('thumbnail')->nullable();
            $table->timestamp('last_opened_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });

        // Studio Patterns (Piano Roll)
        Schema::create('studio_patterns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('studio_projects')->onDelete('cascade');
            $table->string('name');
            $table->string('color')->default('#3B82F6');
            $table->json('notes'); // MIDI notes data
            $table->integer('length')->default(16); // Bars
            $table->timestamps();
        });

        // Studio Tracks (Mixer)
        Schema::create('studio_tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('studio_projects')->onDelete('cascade');
            $table->string('name');
            $table->string('type'); // audio, midi, master
            $table->integer('volume')->default(100);
            $table->integer('pan')->default(0); // -100 to 100
            $table->boolean('muted')->default(false);
            $table->boolean('solo')->default(false);
            $table->json('effects')->nullable(); // VST effects chain
            $table->string('color')->default('#3B82F6');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Studio Audio Clips
        Schema::create('studio_audio_clips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('track_id')->constrained('studio_tracks')->onDelete('cascade');
            $table->string('audio_file');
            $table->integer('start_time')->default(0); // milliseconds
            $table->integer('duration');
            $table->integer('offset')->default(0);
            $table->decimal('pitch', 5, 2)->default(0);
            $table->decimal('speed', 5, 2)->default(1.0);
            $table->timestamps();
        });

        // Studio Instruments (VST)
        Schema::create('studio_instruments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // synth, sampler, drum_machine
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->json('presets')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Studio Effects (VST)
        Schema::create('studio_effects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // reverb, delay, eq, compressor, etc.
            $table->text('description')->nullable();
            $table->json('parameters')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default instruments
        DB::table('studio_instruments')->insert([
            ['name' => '3xOsc', 'type' => 'synth', 'description' => 'Three oscillator synthesizer', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'FL Keys', 'type' => 'synth', 'description' => 'Electric piano', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sytrus', 'type' => 'synth', 'description' => 'FM synthesizer', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'FPC', 'type' => 'drum_machine', 'description' => 'Drum pad controller', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'DirectWave', 'type' => 'sampler', 'description' => 'Sample player', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Seed default effects
        DB::table('studio_effects')->insert([
            ['name' => 'Fruity Reverb 2', 'type' => 'reverb', 'description' => 'Reverb effect', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Fruity Delay 3', 'type' => 'delay', 'description' => 'Delay effect', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Parametric EQ 2', 'type' => 'eq', 'description' => '7-band equalizer', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Fruity Compressor', 'type' => 'compressor', 'description' => 'Dynamic compressor', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Fruity Limiter', 'type' => 'limiter', 'description' => 'Peak limiter', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('studio_effects');
        Schema::dropIfExists('studio_instruments');
        Schema::dropIfExists('studio_audio_clips');
        Schema::dropIfExists('studio_tracks');
        Schema::dropIfExists('studio_patterns');
        Schema::dropIfExists('studio_projects');
        Schema::dropIfExists('zipcode_settings');
        Schema::dropIfExists('zipcode_events');
        Schema::dropIfExists('zipcode_posts');
        Schema::dropIfExists('zipcode_members');
        Schema::dropIfExists('zipcode_owners');
    }
};
