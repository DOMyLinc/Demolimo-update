<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('song_battles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'completed'])->default('active');
            $table->timestamps();
        });

        Schema::create('song_battle_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('song_battle_id')->constrained()->onDelete('cascade');
            $table->integer('version_number'); // 1, 2, 3
            $table->string('file_path');
            $table->string('style_name')->nullable(); // e.g., "Acoustic", "Remix"
            $table->timestamps();
        });

        Schema::create('song_battle_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('song_battle_version_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Ensure a user can only vote once per battle (complex unique constraint handled in logic or here if possible)
            // For now, let's just say one vote per version? No, usually one vote per battle.
            // We'll handle "one vote per battle" in the application logic or a complex index.
        });

        Schema::create('song_battle_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('song_battle_version_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('song_battle_comments');
        Schema::dropIfExists('song_battle_votes');
        Schema::dropIfExists('song_battle_versions');
        Schema::dropIfExists('song_battles');
    }
};
