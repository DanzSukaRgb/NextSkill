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
        Schema::table('quiz_attempt_answers', function (Blueprint $table) {
            $table->unsignedBigInteger('quiz_question_id')->nullable()->change();

            $table->unsignedBigInteger('matching_id')->nullable()->after('quiz_question_id');
            $table->foreign('matching_id')->references('id')->on('quiz_matchings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_attempt_answers', function (Blueprint $table) {
            $table->dropForeign(['matching_id']);
            $table->dropColumn('matching_id');

            $table->unsignedBigInteger('quiz_question_id')->nullable(false)->change();
        });
    }
};
