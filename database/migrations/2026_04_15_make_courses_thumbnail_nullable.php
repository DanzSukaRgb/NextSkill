<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * NOTE: The main 2026_04_15_create_fix_image_storage_issue migration now handles
     * making all image columns nullable, so this migration is kept as a placeholder
     * for historical record.
     */
    public function up(): void
    {
        // Columns are now made nullable in the fix_image_storage_issue migration
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: see up() for details
    }
};
