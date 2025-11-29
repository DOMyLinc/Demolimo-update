<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Audio Ads Table
        Schema::create('audio_ads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('audio_file');
            $table->integer('duration'); // seconds
            $table->string('target_url')->nullable();
            $table->string('target_genre')->nullable(); // Play before songs of this genre
            $table->decimal('budget', 10, 2);
            $table->decimal('cpc_rate', 10, 2)->default(0.10); // Cost per click
            $table->integer('max_plays')->nullable();
            $table->integer('total_plays')->default(0);
            $table->integer('total_clicks')->default(0);
            $table->decimal('total_spent', 10, 2)->default(0);
            $table->string('status')->default('pending'); // pending, active, paused, completed
            $table->timestamps();
        });

        // Ad Analytics Table
        Schema::create('ad_analytics', function (Blueprint $table) {
            $table->id();
            $table->morphs('ad'); // advertisement_id or audio_ad_id
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('event_type'); // impression, click, play
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->foreignId('track_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('cost', 10, 4)->default(0);
            $table->timestamps();

            $table->index(['ad_type', 'ad_id', 'event_type']);
            $table->index('created_at');
        });

        // Update existing advertisements table
        if (!Schema::hasColumn('advertisements', 'cpm_rate')) {
            Schema::table('advertisements', function (Blueprint $table) {
                $table->decimal('cpm_rate', 10, 2)->default(1.00)->after('budget'); // Cost per 1000 impressions
                $table->decimal('cpc_rate', 10, 2)->default(0.50)->after('cpm_rate'); // Cost per click
                $table->string('pricing_model')->default('cpm')->after('cpc_rate'); // cpm or cpc
                $table->integer('max_impressions')->nullable()->after('pricing_model');
                $table->decimal('total_spent', 10, 2)->default(0)->after('budget');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ad_analytics');
        Schema::dropIfExists('audio_ads');

        if (Schema::hasColumn('advertisements', 'cpm_rate')) {
            Schema::table('advertisements', function (Blueprint $table) {
                $table->dropColumn(['cpm_rate', 'cpc_rate', 'pricing_model', 'max_impressions', 'total_spent']);
            });
        }
    }
};
