<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('revenue_shares', function (Blueprint $table) {
            $table->id();
            $table->float('commission_percentage')->default(0);
            $table->float('mentor_revenue_share')->default(80);
            $table->float('platform_revenue_share')->default(20);
            $table->integer('min_withdrawal_amount')->default(100000);
            $table->timestamps();
        });

        DB::table('revenue_shares')->insert([
            'commission_percentage' => 0,
            'mentor_revenue_share' => 80,
            'platform_revenue_share' => 20,
            'min_withdrawal_amount' => 100000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revenue_shares');
    }
};
