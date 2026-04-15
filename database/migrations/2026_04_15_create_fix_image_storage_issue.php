<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Fixes image storage by converting full URLs to relative paths.
     * This ensures images are stored as relative paths only, not full URLs.
     */
    public function up(): void
    {
        // Step 1: Make image columns nullable first (so we can set them to NULL)
        Schema::table('courses', function (Blueprint $table) {
            $table->string('thumbnail')->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->change();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('icon')->nullable()->change();
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->string('file_path')->nullable()->change();
        });

        // Step 2: Fix courses table - convert full URLs to relative paths
        DB::statement("
            UPDATE courses
            SET thumbnail = CASE
                WHEN thumbnail LIKE 'http://%' OR thumbnail LIKE 'https://%' THEN NULL
                ELSE thumbnail
            END
            WHERE thumbnail LIKE 'http://%' OR thumbnail LIKE 'https://%'
        ");

        // Step 3: Fix users table - convert full URLs to relative paths
        DB::statement("
            UPDATE users
            SET avatar = CASE
                WHEN avatar LIKE 'http://%' OR avatar LIKE 'https://%' THEN NULL
                ELSE avatar
            END
            WHERE avatar LIKE 'http://%' OR avatar LIKE 'https://%'
        ");

        // Step 4: Fix categories table - convert full URLs to relative paths
        DB::statement("
            UPDATE categories
            SET icon = CASE
                WHEN icon LIKE 'http://%' OR icon LIKE 'https://%' THEN NULL
                ELSE icon
            END
            WHERE icon LIKE 'http://%' OR icon LIKE 'https://%'
        ");

        // Step 5: Fix lessons table - convert full URLs to relative paths
        DB::statement("
            UPDATE lessons
            SET file_path = CASE
                WHEN file_path LIKE 'http://%' OR file_path LIKE 'https://%' THEN NULL
                ELSE file_path
            END
            WHERE file_path LIKE 'http://%' OR file_path LIKE 'https://%'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cleans up data, so there's no data to restore on rollback
        // Non-reversible migration
    }
};
