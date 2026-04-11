<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!$this->needsConversion()) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            throw new RuntimeException('Konversi primary key ke UUID saat ini hanya didukung untuk MySQL.');
        }

        foreach ($this->foreignConversions() as $conversion) {
            $this->ensureTemporaryUuidColumn($conversion['table'], $conversion['temporary_column']);
            $this->copyForeignKeyValue(
                $conversion['table'],
                $conversion['column'],
                $conversion['temporary_column'],
                $conversion['references']
            );
            $this->assertForeignKeyWasCopied(
                $conversion['table'],
                $conversion['column'],
                $conversion['temporary_column']
            );
        }

        foreach ($this->foreignConversions() as $conversion) {
            $this->dropForeignKey($conversion['table'], $conversion['column']);
        }

        $this->convertPrimaryKey('categories');
        $this->convertPrimaryKey('courses');
        $this->convertPrimaryKey('lessons');

        foreach ($this->foreignConversions() as $conversion) {
            $this->replaceForeignKeyColumn(
                $conversion['table'],
                $conversion['column'],
                $conversion['temporary_column'],
                $conversion['references']
            );
        }
    }

    public function down(): void
    {
        // Non-reversible because integer IDs are removed after conversion.
    }

    private function needsConversion(): bool
    {
        return Schema::hasColumn('categories', 'uuid')
            || Schema::hasColumn('courses', 'uuid')
            || Schema::hasColumn('lessons', 'uuid');
    }

    private function foreignConversions(): array
    {
        return [
            [
                'table' => 'courses',
                'column' => 'category_id',
                'temporary_column' => 'category_uuid',
                'references' => 'categories',
            ],
            [
                'table' => 'lessons',
                'column' => 'course_id',
                'temporary_column' => 'course_uuid',
                'references' => 'courses',
            ],
            [
                'table' => 'quizzes',
                'column' => 'course_id',
                'temporary_column' => 'course_uuid',
                'references' => 'courses',
            ],
            [
                'table' => 'enrollments',
                'column' => 'course_id',
                'temporary_column' => 'course_uuid',
                'references' => 'courses',
            ],
            [
                'table' => 'lesson_progress',
                'column' => 'lesson_id',
                'temporary_column' => 'lesson_uuid',
                'references' => 'lessons',
            ],
            [
                'table' => 'certificates',
                'column' => 'course_id',
                'temporary_column' => 'course_uuid',
                'references' => 'courses',
            ],
        ];
    }

    private function ensureTemporaryUuidColumn(string $tableName, string $columnName): void
    {
        if (Schema::hasColumn($tableName, $columnName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columnName) {
            $table->uuid($columnName)->nullable();
        });
    }

    private function copyForeignKeyValue(
        string $tableName,
        string $foreignKeyColumn,
        string $temporaryColumn,
        string $referenceTable
    ): void {
        $query = sprintf(
            'UPDATE `%s` child JOIN `%s` parent ON child.`%s` = parent.`id` SET child.`%s` = parent.`uuid` WHERE child.`%s` IS NOT NULL',
            $tableName,
            $referenceTable,
            $foreignKeyColumn,
            $temporaryColumn,
            $foreignKeyColumn
        );

        DB::statement($query);
    }

    private function assertForeignKeyWasCopied(
        string $tableName,
        string $foreignKeyColumn,
        string $temporaryColumn
    ): void {
        $missingRows = DB::table($tableName)
            ->whereNotNull($foreignKeyColumn)
            ->whereNull($temporaryColumn)
            ->count();

        if ($missingRows > 0) {
            throw new RuntimeException("Gagal mengonversi foreign key {$tableName}.{$foreignKeyColumn} ke UUID.");
        }
    }

    private function dropForeignKey(string $tableName, string $columnName): void
    {
        $foreignKeyExists = collect(Schema::getForeignKeys($tableName))
            ->contains(function (array $foreignKey) use ($columnName) {
                return ($foreignKey['columns'] ?? []) === [$columnName];
            });

        if (!$foreignKeyExists) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columnName) {
            $table->dropForeign([$columnName]);
        });
    }

    private function convertPrimaryKey(string $tableName): void
    {
        if (!Schema::hasColumn($tableName, 'uuid')) {
            return;
        }

        DB::statement("ALTER TABLE `{$tableName}` MODIFY `id` BIGINT UNSIGNED NOT NULL");
        DB::statement("ALTER TABLE `{$tableName}` DROP PRIMARY KEY");
        DB::statement("ALTER TABLE `{$tableName}` DROP COLUMN `id`");
        DB::statement("ALTER TABLE `{$tableName}` CHANGE `uuid` `id` CHAR(36) NOT NULL");
        DB::statement("ALTER TABLE `{$tableName}` ADD PRIMARY KEY (`id`)");
    }

    private function replaceForeignKeyColumn(
        string $tableName,
        string $foreignKeyColumn,
        string $temporaryColumn,
        string $referenceTable
    ): void {
        if (!Schema::hasColumn($tableName, $temporaryColumn)) {
            return;
        }

        DB::statement("ALTER TABLE `{$tableName}` DROP COLUMN `{$foreignKeyColumn}`");
        DB::statement("ALTER TABLE `{$tableName}` CHANGE `{$temporaryColumn}` `{$foreignKeyColumn}` CHAR(36) NOT NULL");

        Schema::table($tableName, function (Blueprint $table) use ($foreignKeyColumn, $referenceTable) {
            $table->foreign($foreignKeyColumn)->references('id')->on($referenceTable)->cascadeOnDelete();
        });
    }
};
