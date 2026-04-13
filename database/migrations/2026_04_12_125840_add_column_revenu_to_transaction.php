<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if(!Schema::hasColumn('transactions', 'mentor_revenue')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->decimal('mentor_revenue', 15, 2)->default(0)->after('gross_amount');
            });
        }

        if(!Schema::hasColumn('transactions', 'platform_revenue')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->decimal('platform_revenue', 15, 2)->default(0)->after('mentor_revenue');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('mentor_revenue');
            $table->dropColumn('platform_revenue');
        });
    }
};
