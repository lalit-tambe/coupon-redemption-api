<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupon_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();

            // Nullable: guest checkouts have no authenticated user.
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('order_reference')->nullable();

            $table->decimal('amount_discounted', 10, 2);
            $table->timestamp('redeemed_at');

            $table->timestamps();

            // Speeds up the per-coupon / per-user usage-limit lookups that
            // happen on every redemption attempt.
            $table->index(['coupon_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_redemptions');
    }
};
