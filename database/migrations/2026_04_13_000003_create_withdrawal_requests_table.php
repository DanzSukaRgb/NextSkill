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
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->enum('withdrawal_method', ['bank', 'e_wallet']);

            $table->enum('bank_name', ['BRI', 'BCA', 'Mandiri'])->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_holder_name')->nullable();

            $table->enum('e_wallet_type', ['gopay', 'ovo', 'dana', 'shopepay'])->nullable();
            $table->string('e_wallet_number')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->datetime('requested_at')->useCurrent();
            $table->datetime('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
            $table->index('user_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};
