<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        Schema::create('storage_settings', function (Blueprint $table) {
            $table->id();

            // Default Storage Disk
            $table->string('default_disk')->default('local'); // local, s3, spaces, wasabi, backblaze

            // Local Storage
            $table->string('local_path')->default('storage/app/public');

            // Amazon S3
            $table->boolean('s3_enabled')->default(false);
            $table->string('s3_key')->nullable();
            $table->string('s3_secret')->nullable();
            $table->string('s3_region')->nullable();
            $table->string('s3_bucket')->nullable();
            $table->string('s3_url')->nullable();
            $table->string('s3_endpoint')->nullable();

            // DigitalOcean Spaces
            $table->boolean('spaces_enabled')->default(false);
            $table->string('spaces_key')->nullable();
            $table->string('spaces_secret')->nullable();
            $table->string('spaces_region')->nullable();
            $table->string('spaces_bucket')->nullable();
            $table->string('spaces_endpoint')->nullable();

            // Wasabi
            $table->boolean('wasabi_enabled')->default(false);
            $table->string('wasabi_key')->nullable();
            $table->string('wasabi_secret')->nullable();
            $table->string('wasabi_region')->nullable();
            $table->string('wasabi_bucket')->nullable();
            $table->string('wasabi_endpoint')->nullable();

            // Backblaze B2
            $table->boolean('backblaze_enabled')->default(false);
            $table->string('backblaze_key_id')->nullable();
            $table->string('backblaze_app_key')->nullable();
            $table->string('backblaze_bucket')->nullable();
            $table->string('backblaze_region')->nullable();

            // CDN Configuration
            $table->boolean('cdn_enabled')->default(false);
            $table->string('cdn_url')->nullable();
            $table->string('cdn_provider')->nullable(); // cloudflare, cloudfront, etc.

            // Upload Limits
            $table->integer('max_file_size')->default(100); // MB
            $table->integer('max_image_size')->default(5); // MB
            $table->integer('max_audio_size')->default(100); // MB
            $table->integer('max_video_size')->default(500); // MB

            $table->timestamps();
        });

        // Insert default settings
        DB::table('storage_settings')->insert([
            'default_disk' => 'local',
            'local_path' => 'storage/app/public',
            'max_file_size' => 100,
            'max_image_size' => 5,
            'max_audio_size' => 100,
            'max_video_size' => 500,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('storage_settings');
    }
};
