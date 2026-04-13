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
        Schema::create('task_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('submission_link')->nullable();
            $table->text('submission_text')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->integer('score')->nullable();
            $table->text('feedback')->nullable();
            $table->enum('status', ['draft', 'submitted', 'graded'])->default('draft');
            $table->timestamps();

            $table->unique(['task_id', 'user_id']);
            $table->index('task_id');
            $table->index('user_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_submissions');
    }
};
