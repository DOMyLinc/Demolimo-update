<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations to enable Lava theme for admin and frontend.
     */
    public function up(): void
    {
        // Update Lava theme to support admin panel and set as default
        DB::table('themes')
            ->where('name', 'lava')
            ->update([
                    'supports_admin' => true,
                    'is_active' => true,
                    'is_default' => true,
                    'updated_at' => now(),
                ]);

        // Deactivate other themes
        DB::table('themes')
            ->where('name', '!=', 'lava')
            ->update([
                    'is_active' => false,
                    'is_default' => false,
                    'updated_at' => now(),
                ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('themes')
            ->where('name', 'lava')
            ->update([
                    'supports_admin' => false,
                    'updated_at' => now(),
                ]);
    }
};
