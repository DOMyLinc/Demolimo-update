<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('system_updates', function (Blueprint $table) {
            $table->id();
            $table->string('version', 20);
            $table->string('name');
            $table->text('description')->nullable();
            $table->longText('changelog')->nullable();
            $table->string('file_path')->nullable();
            $table->string('backup_path')->nullable();
            $table->string('status')->default('pending'); // pending, installing, completed, failed, rolled_back
            $table->string('requires_version', 20)->nullable();
            $table->json('files_modified')->nullable();
            $table->json('migrations_run')->nullable();
            $table->timestamp('installed_at')->nullable();
            $table->foreignId('installed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('version');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('system_updates');
    }
};
