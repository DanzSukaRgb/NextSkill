<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            if (!Schema::hasColumn('quizzes', 'lesson_id')) {
                $table->char('lesson_id', 36)->nullable()->after('course_id');
                $table->foreign('lesson_id')->references('id')->on('lessons')->onDelete('set null');
            }

            if (!Schema::hasColumn('quizzes', 'type')) {
                $table->enum('type', ['MCQ', 'Matching'])->default('MCQ')->after('title');
            }

            if (!Schema::hasColumn('quizzes', 'quiz_scope')) {
                $table->enum('quiz_scope', ['lesson', 'final'])->default('lesson')->after('type');
            }

            if (!Schema::hasColumn('quizzes', 'total_questions')) {
                $table->integer('total_questions')->default(0)->after('quiz_scope');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn(['lesson_id', 'type', 'quiz_scope', 'total_questions']);
        });
    }
};
