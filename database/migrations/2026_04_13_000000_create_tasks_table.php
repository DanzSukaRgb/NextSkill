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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('course_id')->constrained('courses')->cascadeOnDelete();
            $table->char('lesson_id', 36)->nullable();
            $table->foreign('lesson_id')->references('id')->on('lessons')->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('instruction')->nullable();
            $table->date('due_date')->nullable();
            $table->enum('task_scope', ['lesson', 'final'])->default('lesson');
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->index('course_id');
            $table->index('lesson_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
