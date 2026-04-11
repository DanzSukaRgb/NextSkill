<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropSlugColumn('categories');
        $this->dropSlugColumn('courses');
        $this->dropSlugColumn('lessons');
    }

    public function down(): void
    {
        $this->restoreSlugColumn('categories');
        $this->restoreSlugColumn('courses');
        $this->restoreSlugColumn('lessons');
    }

    private function dropSlugColumn(string $tableName): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        if (Schema::hasColumn($tableName, 'slug')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('slug');
            });
        }
    }

    private function restoreSlugColumn(string $tableName): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        if (!Schema::hasColumn($tableName, 'slug')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('slug')->nullable()->unique();
            });
        }
    }
};
