<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Languages
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // English, Spanish, French
            $table->string('code', 5)->unique(); // en, es, fr
            $table->string('flag')->nullable(); // Flag emoji or icon
            $table->boolean('is_rtl')->default(false); // Right-to-left
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Translations
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('language_id')->constrained()->onDelete('cascade');
            $table->string('group')->default('general'); // general, footer, pages, etc.
            $table->string('key'); // welcome_message, copyright_text
            $table->text('value');
            $table->boolean('is_auto_translated')->default(false);
            $table->timestamps();

            $table->unique(['language_id', 'group', 'key']);
            $table->index(['group', 'key']);
        });

        // CMS Pages
        Schema::create('cms_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('status')->default('published'); // draft, published
            $table->boolean('show_in_footer')->default(false);
            $table->boolean('show_in_header')->default(false);
            $table->integer('footer_order')->default(0);
            $table->integer('header_order')->default(0);
            $table->timestamps();

            $table->index('slug');
            $table->index(['status', 'show_in_footer']);
        });

        // CMS Page Translations
        Schema::create('cms_page_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cms_page_id')->constrained()->onDelete('cascade');
            $table->foreignId('language_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->longText('content');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();

            $table->unique(['cms_page_id', 'language_id']);
        });

        // Footer Settings
        Schema::create('footer_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed default language
        DB::table('languages')->insert([
            'name' => 'English',
            'code' => 'en',
            'flag' => '🇺🇸',
            'is_rtl' => false,
            'is_active' => true,
            'is_default' => true,
            'order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed default footer settings
        DB::table('footer_settings')->insert([
            [
                'key' => 'copyright_text',
                'value' => '© 2024 ' . config('app.name') . '. All rights reserved.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'footer_description',
                'value' => 'The ultimate music streaming platform for artists and listeners.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'social_facebook',
                'value' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'social_twitter',
                'value' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'social_instagram',
                'value' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'social_youtube',
                'value' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Seed default CMS pages
        DB::table('cms_pages')->insert([
            [
                'title' => 'About Us',
                'slug' => 'about-us',
                'content' => '<h1>About Us</h1><p>Welcome to our music streaming platform.</p>',
                'status' => 'published',
                'show_in_footer' => true,
                'footer_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Terms of Service',
                'slug' => 'terms-of-service',
                'content' => '<h1>Terms of Service</h1><p>Please read these terms carefully.</p>',
                'status' => 'published',
                'show_in_footer' => true,
                'footer_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => '<h1>Privacy Policy</h1><p>Your privacy is important to us.</p>',
                'status' => 'published',
                'show_in_footer' => true,
                'footer_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Contact Us',
                'slug' => 'contact-us',
                'content' => '<h1>Contact Us</h1><p>Get in touch with us.</p>',
                'status' => 'published',
                'show_in_footer' => true,
                'footer_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('cms_page_translations');
        Schema::dropIfExists('cms_pages');
        Schema::dropIfExists('footer_settings');
        Schema::dropIfExists('translations');
        Schema::dropIfExists('languages');
    }
};
